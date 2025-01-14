<?php

namespace Give\Tests\Unit\Donors\Repositories;

use Exception;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorMetaRepository;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 *
 * @coversDefaultClass \Give\Donors\Repositories\DonorMetaRepository
 */
class TestDonorMetaRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertMetaShouldInsertNewMeta(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();
        $repository->upsert($donor->id, 'test_key', 'test_value');
        $repository->upsert($donor->id, 'test_key_array', ['Test Value']);
        $repository->upsert($donor->id, 'test_key_int', 1);

        $meta1 = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', 'test_key')
            ->get()
            ->meta_value;

        $meta2 = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', 'test_key_array')
            ->get()
            ->meta_value;

        $meta3 = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', 'test_key_int')
            ->get()
            ->meta_value;

        $this->assertEquals('test_value', $meta1);
        $this->assertEquals(['Test Value'], json_decode($meta2, true));
        $this->assertEquals(1, $meta3);
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertMetaShouldNotDuplicateMeta(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();
        $repository->upsert($donor->id, 'test_key', 'Test Value One');
        $repository->upsert($donor->id, 'test_key', 'Test Value Two');

        $meta = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', 'test_key')
            ->getAll();

        $this->assertCount(1, $meta);
        $this->assertEquals('Test Value Two', $meta[0]->meta_value);
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testUpsertMetaShouldUpdateExistingMeta(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();

        DB::table('give_donormeta')
            ->insert([
                'donor_id' => $donor->id,
                'meta_key' => 'test_key',
                'meta_value' => 'Test Value One',
            ]);

        $repository->upsert($donor->id, 'test_key', 'Test Value Two');

        $meta = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', 'test_key')
            ->get()
            ->meta_value;

        $this->assertEquals('Test Value Two', $meta);
    }

    /**
     * @unreleased
     */
    public function testGetShouldReturnNullIfMetaDoesNotExist(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();

        $meta = $repository->get($donor->id, 'test_key');

        $this->assertNull($meta);
    }

    /**
     * @unreleased
     */
    public function testGetShouldReturnMetaValueIfExists(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();

        DB::table('give_donormeta')
            ->insert([
                'donor_id' => $donor->id,
                'meta_key' => 'test_key',
                'meta_value' => 'Test Value',
            ]);

        $meta = $repository->get($donor->id, 'test_key');
        $this->assertEquals('Test Value', $meta);
    }

    /**
     * @unreleased
     */
    public function testExistsShouldReturnTrue(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();

        DB::table('give_donormeta')
            ->insert([
                'donor_id' => $donor->id,
                'meta_key' => 'test_key',
                'meta_value' => 'Test Value',
            ]);

        $exists = $repository->exists($donor->id, 'test_key');

        $this->assertTrue($exists);
    }

    /**
     * @unreleased
     */
    public function testExistsShouldReturnFalse(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorMetaRepository();

        $exists = $repository->exists($donor->id, 'test_key');

        $this->assertFalse($exists);
    }
}
