# Php developer test
Test for Php developers that want to join our team

## Introduction
Welcome! This is a small test that will help us to know you better as a developer.

The code provided is a real example of a working code just before the final refactor step. The moment when all tests go
green, but you see that the resulting code is not as nice looking as you'd like. You probably know that people say that
a regular coder stops when the code works, but a good developer stops when working code is clean, readable and maintenable.

So we want you to see take that step from regular coder to good developer to figure out how you think and work.

## Getting started
We provide you a basic Vagrant machine configured so you only have to worry about the coding. Just clone the repository,
fire up the Vagrant machine, and make and test your changes (or test and make your changes ;-) ).

If you need any clarification, or maybe find something wrong, just write an issue in this same repository.

### The Lottery entity
The lottery entity it's a stripped down version of the real entity we work with. For the test, you only have to care
about these fields: ``frequency`` and ``draw_time``.

The public interface of the entity is composed mainly by two methods, one (``getLastDrawDate``) for getting the exact
date and time of the most recent past draw, and another (``getNextDrawDate``) one for getting the next one.

As you can see, there's a big code smell in "duplicated" methods, that are almost exactly the same but change a few
details. Making the smell go away is your main job.

#### draw_time
This field stores the hour where the draw is taking place. It's a string in format ``hh:mm:ss`` and it represents the
UTC time.

#### frequency
This field tells us which days are the draws taking place. As you can guess by the name, it doesn't represent an actual
date, but a frequency, and it can have the following formats:

* __y1225__: The draw takes place every year on december 25th
* __m24__: The draw takes place on 24th of each month
* __w0100100__: The draw takes place on tuesdays and fridays
* __w0000001__: The draw takes place on sundays
* __d__: The draw takes place every day.

### Executing the tests
Ssh into the vagrant machine, ``cd /vagrant``, and ``./test.sh``

## What are we looking for
We want you to take the Lottery entity to its completion: refactor to reduce duplicity of code, change any variable
naming that doesn't suit your style, introduce any design pattern you need to do (if you feel like it's needed), add
any extra test case that may be missing, etc.

In short, own the code and improve it.

## How to send your solution

Just do a pull request to the repository
