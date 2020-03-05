CN-Consult CodingStandard
=========================
Our Company-Wide Coding Standard based on PHP_CodeSniffer.

Currently this is targeted for PHP-Code only, but it may also be used to sniff JS-Code or CSS, but we
have no special Sniffs or Code for these languages.

Usage
=====
To use this repository, add it as dev-dependency to your `composer.json`:
`composer require cn-consult/CodingStandard --dev`.

Aftewards create a file `phpcs.xml` in your project root and add the following contents:
```xml
<?xml verison="1.0"?>
<ruleset name="MyProject">
  <description>My cool project, using the CN-Consult PHPCS standard</description>
  <rule ref="CodingStandard" />
  <config name="installed_paths" value="vendor/cn-consult" />
</ruleset>
```
Afterwards you can check your code by executing:
`vendor/bin/phpcs .`


Development
===========

To add new rules to our standard you should open up a youtrack-ticket and explain what you want to accomplish.
You should add some examples that shows what the check should allow and/or disallow.
When you get positive feedback you may implement your check. Before you implement a custom Sniff,
it is a good idea to check if there perhaps already exists a Sniff that accomplishes what you want.
Unfortunatly there is no single documentation that shows all available Sniffs.
A good starting point may be https://github.com/squizlabs/PHP_CodeSniffer/wiki/Customisable-Sniff-Properties or having a
look at the implemented Sniffs by name or searching after the required token in the sourcecode (for example T_FUNCTION).
If there is no other way you may implement your own sniff by following this guide:
https://github.com/squizlabs/PHP_CodeSniffer/wiki/Coding-Standard-Tutorial

After you added your sniff, you can run phpcs through the provided script in `tests/run-manually.sh` and check if it works
and outputs the correct issues for test-files.
If all is like expected please make sure that your new sniff is tested properly.

How to add new good tests
---------------------------
This is really easy. Just add additional files in the `examples/good/` directory
that you don't expect to generate any output, and run the unit-tests.
Your new files will be picked up automatically.


How to add new bad tests
---------------------------
This is a bit more involved. First add an example file in the `examples/bad/` directory
that should trigger some issue. The use the `tests/generate-expected-bad-output.php` to verify that
the file shows the issues you expect and create an accompanying expected-file.
The `generate-expected-bad-output.php` will guide you through this process, just read its output.

