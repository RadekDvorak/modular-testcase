# Modular Test Case #

Modular Test Case is a test-case class for `nette/tester`. It provides
means for improved test method environment setup. 

## Introduction ##
Consider a test that uses a relational database. Appropriate schema
has to be setup before a test is run. Furthermore means have to be
put in place to ensure each test runs in an isolated environment.

Modular Test Case provides a solution for test-case design with such
complex setup requirements. The setup taks is carried out by a _Module_.
Test methods are _annotated_ to select required modules. Required
modules are loaded before the test method executes. They subscribe to
the method's life-cycle events.

In the core of Modular Test Case stands LifeCycle. LifeCycle is
a lightweight mediator whose responsibility is to notify subscribed
modules about test life-cycle changes. There are these life-cycle events
at the moment:

* initialized - called right before the test method is run.
* setUp - called in setUp method; remeber to call parent::setUp() when
overriding the method in a child test-case
* success - called after the test method completes successfully
* exception - called when there is an exception thrown from the test method
* finally - called every time the test method completes
* tearDown - called in tearDown method; remeber to call parent::tearDown() when
overriding the method in a child test-case
* shutDown - called when the process running the test-method finishes

## Configuration ##
Please see integration tests.

## Modules##
### Database setup ###
Usage:
```
@Database(mode="SHARED")
public function testFoo() {}
```

The `"SHARED"` setting uses a single database for all tests.
The database is initialized and the connection is setup on
the initialized event. Tear down or update of the shared
database is not implemented yet.

The `"PRIVATE"` settings creates and tears down a database
per test method. Database is created and the connection is
set up on the initialized event. The database is torn down
on the shutdown event.


### Transaction isolation ###
Recommended usage:
```
@Database(mode="SHARED")
@TransactionIsolation(preventImplicitFlush=TRUE)
public function testFoo() {}
```

This module maintains inter-test database consistency using
transactions. Use after the database has been setup. It is
especially useful for shared database setups provided by
`@Database(mode="SHARED")` annotation.

The `preventImplicitFlush` attribute decides if statements
causing implicit commit are to be rejected (exception is
thrown). See [MySQL Docs](https://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html).

#### Notes ####
The serializable session isolation level is used. Any MySQL
SELECT query is handled as though it contained _FOR UPDATE_.
If multiple tests use the same record, deadlocks may occur.
Create specific test data or isolate the tests in private
databases.

AUTO INCREMENT values are a global state in a shared database.
No specific value whatsoever may be expected by any test. It is
safe to expect the value to increase though.

#### Override your doctrine configuration in tests ####
```
doctrine:
	wrapperClass: Damejidlo\ModularTestCase\Module\TransactionIsolation\UncommittedKdybyConnection
	driverClass: Damejidlo\ModularTestCase\Module\TransactionIsolation\MySqlDriver
```

### Dumper ###
This module is used for internal testing only.

## Notes ##
This library makes use of `doctrine/annotations` - remember to _import_ annotation classes.

Annotations at overridden methods are not loaded.
