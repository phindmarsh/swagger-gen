# swagger-gen
Generates a swagger spec file from routes in a wave app

## Usage

Add this project with composer to an existing [wave](https://github.com/wave-framework/wave) application, then just run `bin/swagger-gen` from the root of the project directory. 

A (hopefully) valid [swagger.io](https://swagger.io) file will be printed to STDOUT. By default this is YAML, but supplying `--format=json` or `--format=php` will output JSON or PHP using var_export respectively.

