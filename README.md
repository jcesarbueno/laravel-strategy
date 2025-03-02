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

# ⚙️ How It Works
Run the following Artisan command:

```bash
    php artisan make:strategy PaymentMethod
```

You will be prompted with interactive questions:

1️⃣ Which methods should it have?
(Enter method names one by one, press Enter on an empty line to finish)

2️⃣ Which concrete implementations should it have?
(Enter class names one by one, press Enter on an empty line to finish)

3️⃣ Do you want to create Pipelines (Chain of Responsibility) for the Strategy?
(Answer yes or no)

# 📁 Generated Structure
For example, if you create a PaymentMethod strategy with methods authorize(), capture(), and refund(), and implementations CreditCard and PayPal, the package will generate:

```swift
app/Strategies/PaymentMethod/
│── Contracts/
│   └── PaymentMethodContract.php
│── Factories/
│   └── PaymentMethodFactory.php
│── Pipelines/
│   └── PaymentMethodPipeline.php  (if selected)
│── Implementations/
│   ├── CreditCard.php
│   ├── PayPal.php
```