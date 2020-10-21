<?php
namespace RowLocker\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use DateTimeImmutable;
use RowLocker\LockableInterface;

/**
 * Contains custom finders
 */
class RowLockerBehavior extends Behavior
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'locked_time' => 'locked_time',
        'locked_session' => 'locked_session',
        'locked_by' => 'locked_by',
        'implementedFinders' => ['unlocked' => 'findUnlocked', 'autoLock' => 'findAutoLock'],
        'implementedMethods' => ['lockingMonitor' => 'lockingMonitor']
    ];

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function implementedEvents(): array
    {
        return [];
    }

    /**
     * Returns the rows that are either unlocker or locked by the provided user
     * This finder requires the `lockingUser` key in options
     *
     * @param Query $quert The Query to modify
     * @param array|ArrayObject $options The options containing the `lockingUser` key
     * @return Query
     */
    public function findUnlocked(Query $query, $options): Query
    {
        return $query->andWhere(function ($exp) use ($options) {
            $timeCol = $this->_config['locked_time'];
            $entityClass = $this->_table->getEntityClass();

            $nullExp = clone $exp;
            $edge = new DateTimeImmutable('@'. (time() - $entityClass::getLockTimeout()));
            $or = $exp->or_([
                $nullExp->isNull($timeCol),
                $exp->lte($timeCol, $edge, 'datetime')
            ]);

            if (!empty($options['lockingUser'])) {
                $or->eq($this->_config['locked_by'], $options['lockingUser']);
            }

            return $or;
        });
    }

    /**
     * Locks all the rows returned by the query.
     * This finder requires the `lockingUser` key in options and optionally the
     * `lockingSession`.
     *
     * @param Query $quert The Query to modify
     * @param array|ArrayObject $options The options containing the `lockingUser` key
     * @return Query
     */
    public function findAutoLock(Query $query, $options): Query
    {
        $by = Hash::get($options, 'lockingUser');
        $session = Hash::get($options, 'lockingSession');

        return $query->formatResults(function ($results) use ($by, $session) {
            $results
                ->filter(function ($r) {
                    return $r instanceof LockableInterface;
                })
                ->each(function ($r) use ($by, $session) {
                    $r->lock($by, $session);
                    $this->_table->save($r);
                });
            return $results;
        });
    }

    /**
     * Returns a callable function. This callable funciton will run inside a safe
     * transaction any other callable function that gets passed to it. The usage
     * of this safe callable function is recommended whenever the `autoLock` finder
     * is used.
     *
     * @return callable
     */
    public function lockingMonitor(): callable
    {
        return function ($callback) {
            $connection = $this->_table->getConnection();
            $level = str_replace('-', ' ', $connection->query('SELECT @@session.tx_isolation')->fetchAll()[0][0]);
            $connection->execute('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE')->closeCursor();

            try {
                return $connection->transactional($callback);
            } finally {
                $connection->execute("SET TRANSACTION ISOLATION LEVEL $level")->closeCursor();
            }
        };
    }
}
