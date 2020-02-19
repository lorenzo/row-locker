# RowLocker plugin for the CakePHP ORM

This plugin offers a simple implementation of row locking by storing a timestamp
in a field of the row and the name of the lock owner.

Row locking can be useful in CMS-like systems where many people try to change
the same record at the same time. By locking the row you can prevent or alert
the users from possible data overwrite.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

```
composer require lorenzo/row-locker
```

**Note:** Above will install package compactible with CakePHP4. Please refer to [Versions section](https://github.com/lorenzo/row-locker#versions) to install package with CakePHP3.

And then enable the plugin:

```
bin/cake plugin load RowLocker
```

## Configuration

Any table to which you wish to apply RowLocker needs to have the following columns:

* `locked_time`: `DATETIME`
* `locked_by` (optional) Can be of any type that identify your users (INT, VARCHAR, UUID...)
* `locked_session` (optional) `VARCHAR(100)` Used for debugging purposes

## Usage

To use RowLocker you first need to add the `LockableInterface` and `LockableTrait` to your entity:

```php
use RowLocker\LockableInterface;
use RowLocker\LockableTrait;
...

class Article extends Entity implements LockableInterface
{
    use LockableTrait;

    ...
}
```

Finally, add the behavior to your Table class:

```php
class ArticlesTable extends Table
{
    public function initialize()
    {
        ...
        $this->addBehavior('RowLocker.RowLocker');
    }
}
```

### Locking Rows

To lock any row first load it and the call `lock()` on it. The lock will last for 5 minutes:

```php
$article = $articlesTable->get($id);
$article->lock($userId, $sessionId); // both arguments are actaully optional
$articlesTable->save($article);
```

RowLocker provides a shortcut for doing the above for one or many rows, by using the
`autoLock` finder:

```php
$article = $articlesTable
    ->findById($id)
    ->find('autoLock', ['lockingUser' => $userId, 'lockingSession' => $sessionId])
    ->firstOrFail(); // This locks the row

$article->isLocked(); // return true
```

### Unlocking a Row

Just call `unlock()` in the entity:

```php
$article->unlock();
$articlesTable->save($article);
```

### Finding Unlocked Rows

In order to find unlocked rows (or with locks owned by the same user), use the `unlocked` finder:


```php
$firstUnlocked = $articlesTable
    ->find('unlocked', ['lockingUser' => $userId])
    ->firstOrFail();
```

### Safely Locking Rows

In systems with high concurrency (many users trying to get a lock of the same row), it is highly
recommended to use the provided `lockingMonitor()` function:

```php
$safeLocker = $articlesTable->lockingMonitor();
// Safely lock the row
$safeLocker(function () use ($id, $userId, $sessionId) {
    $article = $articlesTable
        ->findById($id)
        ->find('autoLock', ['lockingUser' => $userId, 'lockingSession' => $sessionId])
        ->firstOrFail();
});
```

What the locking monitor does is running the inner callable inside a `SERIALIZABLE` transaction.

## Versions

RowLocker has several releases, each compatible with different releases of
CakePHP. Use the appropriate version by downloading a tag, or checking out the
correct branch.

* `1.x` tags are compatible with CakePHP 3.x and greater.
* `2.x` tags is compatible with CakePHP 4.0.x and is stable to use.
