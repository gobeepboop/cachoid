Cachoid
=======
Cachoid provides an expressive API for caching models, collections, and paginators, utilizing the Laravel Taggable Cache Stores.

Additionally, the `ModelObserver` class can be binded in `AppServiceProvider::boot()` using `User::observer(ModelObserver::class)`, as an example. This will keep the cache store in sync with model creates, updates, and deletes.

# ToDo
* Documentation clean-up.
* Extensively more tests!
