# 🎯 Laravel Strategy Package

A Laravel package for generating **Strategy Design Pattern**, along with **Factory** and **Chain of Responsibility** support.  
This package provides an **Artisan command** to quickly scaffold strategies, keeping your Laravel project well-structured.

---

## 🚀 Features

- ✅ **Generates Strategy Pattern classes**
- ✅ **Creates a Factory** for handling strategy instances
- ✅ **Supports Chain of Responsibility (Pipelines)**
- ✅ **Keeps Strategies organized in a dedicated folder**
- ✅ **Fully tested with PestPHP & PHPStan for static analysis**

---

## 📦 Installation

Require the package via Composer:

```bash
composer require jcesarbueno/laravel-strategy
```

---

# ⚙️ How It Works
Run the following Artisan command:

```bash
php artisan make:strategy SendNotification
```

You will be prompted with interactive questions:

1️⃣ Which methods should it have?
(Enter method names one by one, press Enter on an empty line to finish)

2️⃣ Which concrete implementations should it have?
(Enter class names one by one, press Enter on an empty line to finish)

3️⃣ Do you want to create Pipelines (Chain of Responsibility) for the Strategy?
(Answer yes or no)

# 📁 Generated Structure
For example, if you create a SendNotification strategy with method send(), and implementations ApiEvent, SlackEvent and EmailEvent, the package will generate:

```swift
app/Strategies/SendNotification/
│── Contracts/
│   └── SendNotificationContract.php
│── Factories/
│   └── SendNotificationFactory.php
│── Pipelines/
│   └── SendNotificationPipeline.php  (if selected)
│── Implementations/
│   ├── ApiNotification.php
│   ├── SlackNotification.php
│   ├── EmailNotification.php
```

Then, you can use the SendNotificationFactory to get the desired implementation:

```php
use App\Strategies\SendNotification\Factories\SendNotificationFactory;
use App\Models\Customer;

$notificationType = Customer::find(1)->notification_type;

// Choose the implementation in runtime
$sendNotification = SendNotificationFactory::make($notificationType);

$sendNotification->send();
```

You can also use the prebuilt Pipeline to handle the chain of responsibility:

You can create more than one pipeline for the same strategy, each one with a different responsibility. Just copy the SendNotificationPipeline and change the name.

```php
namespace App\Strategies\SendNotification\Pipelines;

use Closure;

class EnsureNotificationTextIsNotEmpty
{
    public function handle($customer, Closure $next)
    {
        if (empty($customer->event->text)) {
            throw new \Exception('Notification text cannot be empty');
        }

        return $next($customer);
    }
}
```

```php
namespace App\Strategies\SendNotification\Pipelines;

use Closure;

class EnsureCustomerHasEmail
{
    public function handle($customer, Closure $next)
    {
        if (empty($customer->email)) {
            throw new \Exception('Customer must have an email');
        }

        return $next($customer);
    }
}
```

Then, you can choose which pipeline to use in each implementation using the function getPipelines():

```php
public function getPipelines(): array
{
    return [
        EnsureNotificationTextIsNotEmpty::class,
        EnsureCustomerHasEmail::class,
    ];
}
```

And another implementation can have a different pipeline:

```php
public function getPipelines(): array
{
    return [
        EnsureNotificationTextIsNotEmpty::class,
    ];
}
```

Now you just call the Pipeline after creating the strategy:

```php
use App\Strategies\SendNotification\Factories\SendNotificationFactory;
use App\Models\Customer;
use Illuminate\Support\Facades\Pipeline;

$customer = Customer::find(1);

$sendNotification = SendNotificationFactory::make($customer->notification_type);

Pipeline::send($customer)
    ->through($sendNotification->getPipelines())
    ->then(function ($customer) use ($sendNotification) {
        $sendNotification->send();
    });
```

## Other Usages for Pipelines

You can use the Pipeline to filter the data before sending it to the strategy, or to handle exceptions in a more organized way.

```php
namespace App\Strategies\SendNotification\Pipelines;

use Illuminate\Support\Collection;
use Closure;

class FilterNotSendedEvents
{
    public function handle(Collection $events, Closure $next)
    {
       $events->filter(function ($event) {
            return $event->sended === false;
        });

        return $next($events);
    }
}
```

Just chain the Pipelines to filter the desired data.

```php
use App\Strategies\SendNotification\Factories\SendNotificationFactory;
use App\Models\Customer;
use Illuminate\Support\Facades\Pipeline;

$customer = Customer::with('events')->find(1);

$sendNotification = SendNotificationFactory::make($customer->notification_type);

$filteredEvents = Pipeline::send($customer->events)
    ->through($sendNotification->getPipelines())
    ->thenReturn();
    
$sendNotification->send($filteredEvents);
```

---

## 📝 License

This package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🧑‍💻 Author

This package was created by Júlio César Bueno, Laravel developer since 2023.


