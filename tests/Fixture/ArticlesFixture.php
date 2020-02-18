<?php
namespace RowLocker\Test\Fixture;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * Short description for class.
 */
class ArticlesFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'title' => ['type' => 'string', 'null' => true],
        'locked_time' => ['type' => 'datetime', 'null' => true],
        'locked_by' => ['type' => 'string', 'null' => true],
        'locked_session' => ['type' => 'string', 'null' => true],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];
    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['title' => 'first'],
        ['title' => 'second'],
        ['title' => 'third'],
    ];
}
