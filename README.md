# additional_reports

[![Latest Stable Version](https://img.shields.io/packagist/v/apen/additional_reports?label=version)](https://packagist.org/packages/apen/additional_reports)
[![Total Downloads](https://img.shields.io/packagist/dt/apen/additional_reports)](https://packagist.org/packages/apen/additional_reports)
[![TYPO3](https://img.shields.io/badge/TYPO3-10.4-orange.svg?style=flat-square)](https://get.typo3.org/version/10)
[![TYPO3](https://img.shields.io/badge/TYPO3-11.5-orange.svg?style=flat-square)](https://get.typo3.org/version/11)
![CI](https://github.com/Apen/additional_reports/workflows/CI/badge.svg)

**Display some useful information in the reports module.**

## ❓ What does it do?

Display some useful information in the reports module, like : 

* list of eID
* list of CommandController and Symfony Commands
* list of all the contents and plugins on the instance (used or not) with filters
* list of xclass declared
* list of hooks declared
* lot of information about the website : TYPO3, System Environment Variables, PHP, MySQL, Apache, Crontab, encoding...
* list of log errors group by number of occurrences (sorting by last time seen or number)
* list of all websites declared with information : domain, sys_template, number of pages...
* list of extensions with information : number of tables, last version date, visual comparison between current and TER extension to see what could be hard coded
* list of all the Middlewares (PSR-15) for frontend and backend

All this tools can really help you during migration or new existing project (to have a global reports of the system).
Do not hesitate to contact me if you have any good ideas.

## ⏳ Installation

Download and install as TYPO3 extension.

* Composer : `composer require apen/additional_reports`
* TER url : https://extensions.typo3.org/extension/additional_reports/
* Releases on Github : https://github.com/Apen/additional_reports/releases

Maybe a "clear cache" is needed, then go to the reports module.

## 💻 Features

### eID

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/eid.png)

### Commands

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/commands.png)

### Plugins and CTypes

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/plugins.png)

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/ctypes.png)

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/summary.png)

### XCLASS

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/xclass.png)

### Hooks

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/hooks.png)

### Global status

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/status1.png)
![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/status2.png)

### Grouped logs

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/logs.png)

### List of websites and domains

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/websites.png)

### Extensions

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/extensions.png)

Text diff with TER and last version

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/extensions-diff.png)

### EventDispatcher (PSR-14)

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/eventdispatcher.png)

### Middlewares (PSR-15)

![](https://raw.githubusercontent.com/Apen/additional_reports/master/Resources/Public/Images/middlewares.png)
