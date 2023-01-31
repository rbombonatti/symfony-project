<?php

namespace App\Test\Controller;

use App\Entity\Hashes;
use App\Repository\HashesRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HashesControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private HashesRepository $repository;
    private string $path = '/hashes/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Hashes::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Hash index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'hash[dateTimeBatch]' => 'Testing',
            'hash[blockNumber]' => 'Testing',
            'hash[entryString]' => 'Testing',
            'hash[generatedKey]' => 'Testing',
            'hash[generatedHash]' => 'Testing',
            'hash[generationAttempts]' => 'Testing',
            'hash[userIpAddress]' => 'Testing',
        ]);

        self::assertResponseRedirects('/hashes/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Hashes();
        $fixture->setDateTimeBatch('My Title');
        $fixture->setBlockNumber('My Title');
        $fixture->setEntryString('My Title');
        $fixture->setGeneratedKey('My Title');
        $fixture->setGeneratedHash('My Title');
        $fixture->setGenerationAttempts('My Title');
        $fixture->setUserIpAddress('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Hash');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Hashes();
        $fixture->setDateTimeBatch('My Title');
        $fixture->setBlockNumber('My Title');
        $fixture->setEntryString('My Title');
        $fixture->setGeneratedKey('My Title');
        $fixture->setGeneratedHash('My Title');
        $fixture->setGenerationAttempts('My Title');
        $fixture->setUserIpAddress('My Title');

        $this->repository->save($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'hash[dateTimeBatch]' => 'Something New',
            'hash[blockNumber]' => 'Something New',
            'hash[entryString]' => 'Something New',
            'hash[generatedKey]' => 'Something New',
            'hash[generatedHash]' => 'Something New',
            'hash[generationAttempts]' => 'Something New',
            'hash[userIpAddress]' => 'Something New',
        ]);

        self::assertResponseRedirects('/hashes/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getDateTimeBatch());
        self::assertSame('Something New', $fixture[0]->getBlockNumber());
        self::assertSame('Something New', $fixture[0]->getEntryString());
        self::assertSame('Something New', $fixture[0]->getGeneratedKey());
        self::assertSame('Something New', $fixture[0]->getGeneratedHash());
        self::assertSame('Something New', $fixture[0]->getGenerationAttempts());
        self::assertSame('Something New', $fixture[0]->getUserIpAddress());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Hashes();
        $fixture->setDateTimeBatch('My Title');
        $fixture->setBlockNumber('My Title');
        $fixture->setEntryString('My Title');
        $fixture->setGeneratedKey('My Title');
        $fixture->setGeneratedHash('My Title');
        $fixture->setGenerationAttempts('My Title');
        $fixture->setUserIpAddress('My Title');

        $this->repository->save($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/hashes/');
    }
}
