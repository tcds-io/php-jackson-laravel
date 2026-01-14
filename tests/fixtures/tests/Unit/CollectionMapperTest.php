<?php

namespace Test\Tcds\Io\Jackson\Laravel\fixtures\tests\Unit;

use App\Queries\InvoiceQuery;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tcds\Io\Jackson\JsonObjectMapper;
use Tcds\Io\Jackson\Laravel\Mappers\CollectionMapper;

class CollectionMapperTest extends TestCase
{
    private const string JSON_CONTENT = <<<JSON
    [
      {"userId": 10, "customer": "Arthur Dent"},
      {"userId": 15, "customer": "Ford Prefect"}
    ]
    JSON;

    #[Test]
    public function collection_reader_and_writer(): void
    {
        $type = Collection::class;
        $mappers = CollectionMapper::get($type);

        $reader = $mappers[$type]['reader'];
        $writer = $mappers[$type]['writer'];

        $this->assertEquals(CollectionMapper::read(...), $reader);
        $this->assertEquals(CollectionMapper::write(...), $writer);
    }

    #[Test]
    public function read_from_collection_mapper_reader(): void
    {
        $mapper = new JsonObjectMapper();

        $collection = CollectionMapper::read(
            data: self::JSON_CONTENT,
            type: generic(Collection::class, [InvoiceQuery::class]),
            mapper: $mapper,
            path: [],
        );

        $this->assertEquals(
            new Collection([
                new InvoiceQuery(userId: 10, customer: 'Arthur Dent'),
                new InvoiceQuery(userId: 15, customer: 'Ford Prefect'),
            ]),
            $collection,
        );
    }

    #[Test]
    public function read_from_object_mapper(): void
    {
        $mapper = new JsonObjectMapper(typeMappers: [...CollectionMapper::get(Collection::class)]);

        $collection = $mapper->readValue(
            type: generic(Collection::class, [InvoiceQuery::class]),
            value: self::JSON_CONTENT,
        );

        $this->assertEquals(
            new Collection([
                new InvoiceQuery(userId: 10, customer: 'Arthur Dent'),
                new InvoiceQuery(userId: 15, customer: 'Ford Prefect'),
            ]),
            $collection,
        );
    }

    #[Test]
    public function write_from_collection_mapper_reader(): void
    {
        $mapper = new JsonObjectMapper();

        $json = CollectionMapper::write(
            data: new Collection([
                new InvoiceQuery(userId: 10, customer: 'Arthur Dent'),
                new InvoiceQuery(userId: 15, customer: 'Ford Prefect'),
            ]),
            type: generic(Collection::class, [InvoiceQuery::class]),
            mapper: $mapper,
            path: [],
        );

        $this->assertJsonStringEqualsJsonString(
            <<<JSON
            [
              {"userId": 10, "customer": "Arthur Dent"},
              {"userId": 15, "customer": "Ford Prefect"}
            ]
            JSON,
            $json,
        );
    }

    #[Test]
    public function write_from_object_mapper(): void
    {
        $mapper = new JsonObjectMapper(typeMappers: [...CollectionMapper::get(Collection::class)]);

        $json = $mapper->writeValue(
            value: new Collection([
                new InvoiceQuery(userId: 10, customer: 'Arthur Dent'),
                new InvoiceQuery(userId: 15, customer: 'Ford Prefect'),
            ]),
            type: generic(Collection::class, [InvoiceQuery::class]),
        );

        $this->assertJsonStringEqualsJsonString(
            <<<JSON
            [
              {"userId": 10, "customer": "Arthur Dent"},
              {"userId": 15, "customer": "Ford Prefect"}
            ]
            JSON,
            $json,
        );
    }
}
