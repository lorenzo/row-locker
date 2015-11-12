<?php
namespace RowLocker;

use Cake\Database\Type;

/**
 * Default implementation for LockableInterface
 *
 */
trait LockableTrait
{
    protected static $lockTimeout = 300;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function lock($by = null, $session = null)
    {
        if ($this->isLocked() && $by !== $this->lockOwner()) {
            throw new LockingException('This entity is already locked');
        }

        $this->set('locked_time', Type::build('datetime')->marshal(time()));
        if ($by !== null) {
            $this->set('locked_by', $by);
        }

        if ($session !== null) {
            $this->set('locked_session', $session);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function unlock()
    {
        $this->set([
            'locked_by' => null,
            'locked_session' => null,
            'locked_time' => null
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isLocked()
    {
        $now = time();
        $locked = $this->get('locked_time');
        $locked = $locked ? $locked->format('U') : 0;

        return $locked && abs($now - $locked) < static::getLockTimeout();
    }

    /**
     * {@inheritDoc}
     *
     * @return string|null
     */
    public function lockOwner()
    {
        return $this->get('locked_by');
    }

    /**
     * {@inheritDoc}
     *
     * @return string|null
     */
    public function lockSession()
    {
        return $this->get('locked_session');
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public static function setLockTimeout($seconds)
    {
        static::$lockTimeout = (int)$seconds;
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public static function getLockTimeout()
    {
        return static::$lockTimeout;
    }
}
