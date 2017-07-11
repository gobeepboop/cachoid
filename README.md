Cachoid
=======
Cachoid provides an expressive API for caching models, collections, and paginators, utilizing the Laravel Taggable Cache Stores.

*Documentation is currently under work.*

# Installation

First, install Cachoid via the Composer Package manager:
```
composer require beep/cachoid
```

Next, you should add the `CachoidServiceProvider` to the `providers` array of your `config/app.php` configuration file:
```php
\Beep\Cachoid\CachoidServiceProvider::class,
```

Finally, add the `Beep\Cachoid\Cacheable` trait to the model you would like to make cacheable. This trait will register a model observer to keep the model synchronized with Cachoid:
```php
<?php

namespace App;

use Beep\Cachoid\Cacheable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Cacheable;
}
```

# Configuration

## Configuring Model Identifiers
By default, each Eloquent model is synced by its model identifier, and through the Cache driver. If you would like to customize the identifier, you may override the `cacheableAs` method on the Model.

```php
<?php

namespace App;

use Beep\Cachoid\Cacheable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Cacheable;

    /**
     * Get the identifier to cache as.
     *
     * @return mixed
     */
    public function cacheableAs()
    {
        return $this->getKey();
    }
}
```

# Eloquent Models
## Retrieving Models by Identifier
`remember` allows us to easily cache an Eloquent model. When a record is fresh, it will instead retrieve it:

```php
$user = $cachoid->eloquent()->withName(User::class)->identifiedBy(1)->remember(15, function () {
    return User::find(1);
});
```

Or more simply put:
```php
$user = $cachoid->eloquent(User::class, 1)->remember(15, function () {
    return User::find(1);
});

```

# Paginators
`remember` and `rememberForever` allow us to easily cache an entire paginator of data (e.g. Eloquent Models). When models are within the paginator, they are uniquely tagged with a lowercase, snake cased name of the class hyphenated with the identifer.
```php
$paginator = $cachoid->paginator(User::class)->remember(15, function () {
    return User::paginate();
});
```
The `remember` method will cache the paginator unless it already exists in the cache.

Of course, you may add uniqueness such as the page and results per page.


```php
$paginator = $cachoid->paginator(User::class)->onPage(1)->showing(15)->remember(15, function () {
    return User::paginate();
});
```

Or more simply put...
```php
$paginator = $cachoid->paginator(User::class, 15, 1)->remember(15, function () {
    return User::paginate();
});
```

## ToDo
* Documentation clean-up.
* An extensive set of tests, with Mockery, and a build-up of the SQLite "feature" tests (such as ModelObserverTest)
* Current page resolution for the PaginatorAdapter.
