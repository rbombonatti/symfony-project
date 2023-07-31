<?php

namespace App\Controller;

use App\Entity\Hashes;
use App\Form\HashesType;
use App\Repository\HashesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/hashes')]
class HashesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public int $next_block;

    #[Route('/', name: 'app_hashes_index', methods: ['GET', 'POST'])]
    public function index(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $em = $this->entityManager;
        $allHashesRepository = $em->getRepository(Hashes::class);
        $attemptQuery = $request->request->get('attemptQuery');

        if (!$attemptQuery) $attemptQuery = 1000000;

        $allHashesQuery = $allHashesRepository
                    ->createQueryBuilder('h')
                    ->orderBy('h.blockNumber', 'DESC')
                    ->where('h.generationAttempts < :attemptQuery')
                    ->setParameter('attemptQuery', $attemptQuery)
                    ->getQuery();

        $allHashes = $paginator->paginate(
                            $allHashesQuery,
                            $request->query->getInt('page', 1), 
                            10 
                        );

        return $this->render('hashes/index.html.twig', 
            [
                'allHashes' => $allHashes,
                'attemptQuery' => $attemptQuery
            ]
        );
    }

    #[Route('/create/{entryString}/{requestNumber}', name: 'app_hashes_create', methods: ['GET'])]
    public function create($entryString, $requestNumber, HashesRepository $hashesRepository, Request $request, RateLimiterFactory $anonymousApiLimiter): Response
    {
        $limiter = $anonymousApiLimiter->create($request->getClientIp());
        $limit = $limiter->consume();

        $headers = [
            'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
            'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp(),
            'X-RateLimit-Limit' => $limit->getLimit(),
        ];

        if (false === $limit->isAccepted()) {
           return new Response(null, Response::HTTP_TOO_MANY_REQUESTS, $headers);
        }

        $this->generateHashCascate($entryString, $requestNumber, $hashesRepository);

        return $this->redirectToRoute('app_hashes_index', [], Response::HTTP_SEE_OTHER);
    }

    public function generateHashCascate(string $entryString, int $requestNumber, HashesRepository $hashesRepository)
    {
        $hashes = new Hashes();
        $hashReturned = [];
        $cont = 0;
        $entryStringUsed = $entryString;
        $this->next_block = $hashesRepository->getNextBlockNumber();

        do {
            $hashReturned = $this->getQualifiedHash($entryStringUsed, $hashes, $hashesRepository);    
            $hashesRepository->save($hashReturned, true);
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
        } while (substr($generatedHash, 0, 4) !== '0000');

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

    private function concatenateStrings($receivedString, $key)
    {
        return $receivedString . $key;
    }

    private function getHash($key, $receivedString) 
    {
        $completeString = $this->concatenateStrings($receivedString, $key);
        return $this->md5Generator($completeString);
    }

    private function md5Generator($inputedString) 
    {
        return md5($inputedString);
    }   

    public function addHash(Hashes $hash, HashesRepository $hashesRepository)
    {
        $hashesRepository->save($hash, true);
        return true;
    }

}
