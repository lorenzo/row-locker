<?php
namespace RowLocker\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use RowLocker\LockableInterface;
use RowLocker\LockableTrait;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class TestEntity extends Entity implements LockableInterface
{
    use LockableTrait;
}


/**
 * RowLocker\Model\Behavior\RowLockerBehavior Test Case
 */
class RowLockerBehaviorTest extends TestCase
{

    public $fixtures = [
        'plugin.RowLocker.Articles'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->table = TableRegistry::get('Articles', ['entityClass' => TestEntity::class]);
        $this->table->addBehavior('RowLocker.RowLocker');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testUnlocked()
    {
        $results = $this->table
            ->find('unlocked', ['lockingUser' => 'lorenzo'])
            ->toArray();
        $this->assertCount(3, $results);

        $article = $results[0];
        $article->lock('someone-else');
        $this->table->save($article);

        $results = $this->table
            ->find('unlocked', ['lockingUser' => 'lorenzo'])
            ->toArray();
        $this->assertCount(2, $results);

        $results = $this->table
            ->find('unlocked', ['lockingUser' => 'someone-else'])
            ->toArray();
        $this->assertCount(3, $results);
    }

    public function testLockingMonitor()
    {
        // SQLite has error
        $this->expectException('PDOException');
        $this->expectExceptionMessage('SQLSTATE[HY000]: General error: 1 unrecognized token: "@"');

        $safeLocker = $this->table->lockingMonitor();
        $safeLocker(function() {
            $article = $this->table
                ->find('autoLock', ['lockingUser' => 'lorenzo', 'lockingSession' => 'session-id'])
                ->firstOrFail();

            $this->assertNotEmpty($article);
        });
    }
}
