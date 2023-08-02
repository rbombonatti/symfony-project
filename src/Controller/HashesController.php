<?php

namespace App\Controller;

use App\Entity\Hashes;
use App\Repository\HashesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

#[Route('/hashes')]
class HashesController extends AbstractController
{

    public int $next_block;
    const MAX_REQUEST_NUMBER = 200;
    const LINES_PER_PAGE = 10;
    const DEFAULT_VALUE_SEARCH = 100000000;
    const HASH_QUALIFICATOR = '0000';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaginatorInterface $paginator,
        private HashesRepository $hashesRepository,
        private ManagerRegistry $doctrine,
        private RateLimiterFactory $anonymousApiLimiter,
    ) {}

    #[Route('/', name: 'app_hashes_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $allHashesRepository = $this->entityManager->getRepository(Hashes::class);

        if ($request->isMethod('POST')) {
            $page =  '1';
            $attemptQuery = $request->request->get('attemptQuery');
        } else {
            $page =  $request->query->getInt('page', 1);
            $attemptQuery = $request->get('attemptQuery');
        }

        if (!$attemptQuery) $attemptQuery = self::DEFAULT_VALUE_SEARCH ;

        $allHashesQuery = $allHashesRepository
                    ->createQueryBuilder('h')
                    ->orderBy('h.blockNumber', 'DESC')
                    ->where('h.generationAttempts < :attemptQuery')
                    ->setParameter('attemptQuery', $attemptQuery)
                    ->getQuery();

        $allHashes = $this->paginator->paginate(
                            $allHashesQuery,
                            $page,
                            self::LINES_PER_PAGE
                        );
        return $this->render('hashes/index.html.twig', compact('allHashes', 'attemptQuery'));
    }

    #[Route(
        '/create/{entryString}/{requestNumber}', 
        name: 'app_hashes_create', 
        methods: ['GET'],
        requirements: ['requestNumber' => '[0-9]+']
        )]
    public function create($entryString, $requestNumber, Request $request): Response
    {
        $this->checkEntryValues($entryString, $requestNumber);
        $limiter = $this->anonymousApiLimiter->create($request->getClientIp());
        $limit = $limiter->consume();

        if (false === $limit->isAccepted()) {
            $headers = [
                'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
                'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp(),
                'X-RateLimit-Limit' => $limit->getLimit(),
            ];
            return new Response(null, Response::HTTP_TOO_MANY_REQUESTS, $headers);
        }

        $this->generateHashCascate($entryString, $requestNumber);
        return $this->redirectToRoute('app_hashes_index', [], Response::HTTP_SEE_OTHER);
    }

    public function generateHashCascate(string $entryString, int $requestNumber)
    {
        $hashes = new Hashes();
        $hashReturned = [];
        $cont = 0;
        $entryStringUsed = $entryString;
        $this->next_block = $this->hashesRepository->getNextBlockNumber();

        do {
            $hashReturned = $this->getQualifiedHash($entryStringUsed, $hashes);
            $this->hashesRepository->save($hashReturned, true);
            $cont++;
            if ($cont > 0) {
                $entryStringUsed = $hashReturned->getGeneratedHash();
            }
        } while ($cont < $requestNumber);
    }

    private function getQualifiedHash($entryString)
    {
        $generationAttempts = 0;
        do {
            $generatedKey = $this->generateStringPrefix(8);
            $generatedHash = $this->getHash($entryString, $generatedKey); 
            $generationAttempts++;
        } while (substr($generatedHash, 0, 4) !== self::HASH_QUALIFICATOR);

        $hashReturned = new Hashes();
        $hashReturned->setDateTimeBatch(new \DateTime('@'.strtotime('now'), new \DateTimeZone('America/Sao_Paulo')));
        $hashReturned->setEntryString($entryString);
        $hashReturned->setGeneratedHash($generatedHash);
        $hashReturned->setGeneratedKey($generatedKey);
        $hashReturned->setGenerationAttempts($generationAttempts);
        $hashReturned->setBlockNumber($this->next_block);
        return $hashReturned;
    }

    private function generateStringPrefix($length) 
    {
        $stringUniverse = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $stringLength = strlen($stringUniverse);
        $generatedString = '';
        for ($i = 0; $i < $length; $i++) {
            $generatedString .= $stringUniverse[rand(0, $stringLength -1)];
        }
        return $generatedString;
    }

    private function getHash($receivedString, $key) 
    {
        return md5($receivedString . $key);
    }

    public function checkEntryValues($entryString, $requestNumber) 
    {
        if (!$entryString || !ctype_alnum($entryString)) 
            throw new Exception('Variável de entrada deve conter apenas letras e números. Valor rejeitado: ' . $entryString);

        if (!$requestNumber || !is_numeric($requestNumber)) 
            throw new Exception('Quantidade deve ser numérico. Valor rejeitado: ' . $requestNumber);

        if (is_numeric($requestNumber) && ($requestNumber > self::MAX_REQUEST_NUMBER)) 
            throw new Exception('Limite máximo de solicitações é ' . self::MAX_REQUEST_NUMBER . '. Valor solicitado: ' . $requestNumber);
    }

    public function addHash(Hashes $hash)
    {
        $this->hashesRepository->save($hash, true);
        return true;
    }

}
