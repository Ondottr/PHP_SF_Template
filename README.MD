## THIS IS A FRAMEWORK TEMPLATE FOR PHP SF

**<h4>The Framework is [located here](https://github.com/Ondottr/PHP_SF_platform) </h4>**

## Table of contents

- [Repository structure](#php-sf-repository-structure)
- [Installation](#quickstart--installation)

## PHP SF repository structure

PHP SF project consists of multiple repositories, two of them are important to you:

- `PHP_SF/platform`
    - This is where the PHP SF core is developed. You need it as dependency in your projects
    - This is where you can participate in the development of Shopware through pull requests
- [`PHP_SF/template`](https://github.com/Ondottr/PHP_SF_template) is the template
    - **This is where your journey with PHP SF starts**
    - Installation see below!

## Quickstart / Installation

Let's start by [Installing Symfony](https://symfony.com/doc/current/setup.html) and cloning the template:

```bash
git clone git@github.com:Ondottr/PHP_SF_template.git .
```

You now have the application template installed in the current directory

Next, you need to clone the Framework:

```bash
git clone https://github.com/Ondottr/PHP_SF_platform Platform
```

Now, that you have Framework installed in the _**Platform**_ directory, here is your next steps:

```bash
composer install
cd Platform 
composer install
```

The final step is creating the constants file with environment variables and write down all important data:

```bash
cp configs/constants.example.php ../configs/constants.php
```

Now, you can start your application using symfony:

```bash
symfony serve -d
```
