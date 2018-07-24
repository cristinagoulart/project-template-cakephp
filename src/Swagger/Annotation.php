<?php
namespace App\Swagger;

use Cake\Core\App;
use Cake\Database\Exception;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Qobo\Utils\Utility;

class Annotation
{
    /**
     * Default property type
     */
    const DEFAULT_TYPE = 'string';

    /**
     * Annotation content.
     *
     * @var string
     */
    protected $_content = null;

    /**
     * Mapping of database column types to swagger types.
     *
     * @var array
     */
    protected $_db2swagger = [
        'uuid' => [
            'type' => 'string',
            'format' => 'uuid'
        ],
        'string' => [
            'type' => 'string'
        ],
        'text' => [
            'type' => 'string'
        ],
        'boolean' => [
            'type' => 'boolean'
        ],
        'datetime' => [
            'type' => 'string',
            'format' => 'date-time'
        ],
        'date' => [
            'type' => 'string',
            'format' => 'date'
        ],
        'time' => [
            'type' => 'string',
            'format' => 'time'
        ],
        'decimal' => [
            'type' => 'number',
            'format' => 'float'
        ],
        'integer' => [
            'type' => 'integer'
        ],
    ];

    /**
     * Class name to generate annotations for.
     *
     * @var string
     */
    protected $_className = '';

    /**
     * Full path name of the file to generate annotations for.
     *
     * @var string
     */
    protected $_path = '';

    /**
     * Flag for including Swagger Info annotation.
     *
     * @var bool
     */
    protected $withInfo = false;

    /**
     * Swagger annotations.
     *
     * @var array
     */
    protected $_annotations = [
        'info' => '/**
            @SWG\Swagger(
                @SWG\Info(
                    title="API Documentation",
                    description="Interactive API documentation powered by Swagger.io",
                    termsOfService="http://swagger.io/terms/",
                    version="{{version}}"
                ),
                @SWG\SecurityScheme(
                    securityDefinition="Bearer",
                    description="Json Web Tokens (JWT)",
                    type="apiKey",
                    name="token",
                    in="query"
                )
            )
        */',
        'definition' => '/**
            @SWG\Definition(
                definition="{{definition}}",
                required={"{{required}}"},
                {{properties}}
            )
         */',
        'property' => '
            @SWG\Property(
                property="{{property}}",
                {{options}}
            )',
        'paths' => '/**
            @SWG\Get(
                path="/api/{{module_url}}",
                summary="Retrieve a list of {{module_human_plural}}",
                tags={"{{module_human_plural}}"},
                consumes={"application/json"},
                produces={"application/json"},
                @SWG\Parameter(
                    name="limit",
                    description="Results limit",
                    in="query",
                    required=false,
                    type="integer",
                    default=""
                ),
                @SWG\Parameter(
                    name="sort",
                    description="Sort results by field",
                    in="query",
                    required=false,
                    type="string",
                    enum={ {{sort_fields}} }
                ),
                @SWG\Parameter(
                    name="direction",
                    description="Sorting direction",
                    in="query",
                    required=false,
                    type="string",
                    enum={"asc", "desc"}
                ),
                @SWG\Response(
                    response="200",
                    description="Successful operation",
                    @SWG\Schema(
                        ref="#/definitions/{{module_singular}}"
                    )
                )
            )

            @SWG\Get(
                path="/api/{{module_url}}/view/{id}",
                summary="Retrieve a {{module_human_singular}} by ID",
                tags={"{{module_human_plural}}"},
                consumes={"application/json"},
                produces={"application/json"},
                @SWG\Parameter(
                    name="id",
                    description="{{module_human_singular}} ID",
                    in="path",
                    required=true,
                    type="string",
                    default=""
                ),
                @SWG\Response(
                    response="200",
                    description="Successful operation",
                    @SWG\Schema(
                        ref="#/definitions/{{module_singular}}"
                    )
                ),
                @SWG\Response(
                    response="404",
                    description="Not found"
                )
            )

            @SWG\Post(
                path="/api/{{module_url}}/add",
                summary="Add new {{module_human_singular}}",
                tags={"{{module_human_plural}}"},
                consumes={"application/json"},
                produces={"application/json"},
                @SWG\Parameter(
                    name="body",
                    in="body",
                    description="{{module_human_singular}} object to be added to the system",
                    required=true,
                    @SWG\Schema(ref="#/definitions/{{module_singular}}")
                ),
                @SWG\Response(
                    response="201",
                    description="Successful operation"
                )
            )

            @SWG\Put(
                path="/api/{{module_url}}/edit/{id}",
                summary="Edit an existing {{module_human_singular}}",
                tags={"{{module_human_plural}}"},
                consumes={"application/json"},
                produces={"application/json"},
                @SWG\Parameter(
                    name="id",
                    description="{{module_human_singular}} ID",
                    in="path",
                    required=true,
                    type="string",
                    default=""
                ),
                @SWG\Parameter(
                    name="body",
                    in="body",
                    description="{{module_human_singular}} name",
                    required=true,
                    @SWG\Schema(ref="#/definitions/{{module_singular}}")
                ),
                @SWG\Response(
                    response="200",
                    description="Successful operation"
                ),
                @SWG\Response(
                    response="404",
                    description="Not found"
                )
            )

            @SWG\Delete(
                path="/api/{{module_url}}/delete/{id}",
                summary="Delete a {{module_human_singular}}",
                tags={"{{module_human_plural}}"},
                consumes={"application/json"},
                produces={"application/json"},
                @SWG\Parameter(
                    name="id",
                    description="{{module_human_singular}} ID",
                    in="path",
                    required=true,
                    type="string",
                    default=""
                ),
                @SWG\Response(
                    response="200",
                    description="Successful operation"
                ),
                @SWG\Response(
                    response="404",
                    description="Not found"
                )
            )
        */'
    ];

    /**
     * Constructor method.
     *
     * @param string $className Class name
     * @param string $path File path
     * @param bool $withInfo Info annotation flag
     * @return void
     */
    public function __construct($className, $path, $withInfo = false)
    {
        $this->_className = $className;

        $this->_path = $path;
        $this->withInfo = $withInfo;
    }

    /**
     * Swagger annotation content getter.
     *
     * @return string
     */
    public function getContent()
    {
        if (empty($this->_content)) {
            $this->_generateContent();
        }

        return $this->_content;
    }

    /**
     * Swagger annotation content setter.
     *
     * @param string $content The content
     * @return void
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }

    /**
     * Method that generates and sets swagger annotation content.
     *
     * @return void
     */
    protected function _generateContent()
    {
        $result = file_get_contents($this->_path);

        $info = $this->getInfo();

        $properties = $this->_getProperties();

        $definition = $this->_getDefinition($properties);

        $paths = $this->_getPaths();

        $result = preg_replace('/(^class\s)/im', implode("\n", [$info, $definition, $paths]) . "\n$1", $result);

        $this->setContent(trim($result));
    }

    /**
     * Swagge Info annotation generator.
     *
     * @return string
     */
    protected function getInfo()
    {
        if (! $this->withInfo) {
            return '';
        }

        $versions = Utility::getApiVersions(App::path('Controller/Api')[0]);

        $placeholders = [
            '{{version}}' => $versions[0]['number']
        ];

        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->_annotations['info']
        );
    }

    /**
     * Generates and returns swagger properties annotation.
     *
     * It uses current table's column definitions to generate
     * swagger property annotation on the fly.
     *
     * @return string
     */
    protected function _getProperties()
    {
        $table = TableRegistry::get($this->_className);

        $entity = $table->newEntity();
        $hiddenProperties = $entity->hiddenProperties();
        try {
            $columns = $table->schema()->columns();
            $columns = array_diff($columns, $hiddenProperties);
        } catch (Exception $e) {
            return '';
        }

        $result = [];
        foreach ($columns as $column) {
            $placeholders = [
                '{{property}}' => $column,
                '{{options}}' => $this->getPropertyOptionsAsString($column, $table->schema()->getColumn($column))
            ];

            $result[] = str_replace(
                array_keys($placeholders),
                array_values($placeholders),
                $this->_annotations['property']
            );
        }

        return implode(',', $result);
    }

    /**
     * Returns property options as stirng.
     *
     * @param string $column Column name
     * @param array $data Column data
     * @return string
     */
    protected function getPropertyOptionsAsString($column, array $data)
    {
        $result = [];
        foreach ($this->getPropertyOptions($column, $data['type']) as $key => $value) {
            switch (gettype($value)) {
                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;

                case 'string':
                    $value = '"' . $value . '"';
                    break;
            }

            $result[] = sprintf('%s=%s', $key, $value);
        }

        return implode(',', $result);
    }

    /**
     * Returns property options.
     *
     * @param string $column Column name
     * @param string $type Column data
     * @return array
     */
    protected function getPropertyOptions($column, $type)
    {
        return [
            'type' => $this->getPropertyType($type),
            'format' => $this->getPropertyFormat($type),
            'example' => $this->getPropertyExample($type)
        ];
    }

    /**
     * Returns property Swagger type.
     *
     * @param property $type Column type
     * @return string
     */
    protected function getPropertyType($type)
    {
        $type = array_key_exists($type, $this->_db2swagger) ? $type : static::DEFAULT_TYPE;

        return $this->_db2swagger[$type]['type'];
    }

    /**
     * Returns property Swagger format.
     *
     * @param property $type Column type
     * @return string
     */
    protected function getPropertyFormat($type)
    {
        $type = array_key_exists($type, $this->_db2swagger) ? $type : static::DEFAULT_TYPE;

        return array_key_exists('format', $this->_db2swagger[$type]) ?
            $this->_db2swagger[$type]['format'] :
            $this->getPropertyType($type);
    }

    /**
     * Property example getter.
     *
     * @param [type] $type [description]
     * @return [type] [description]
     */
    protected function getPropertyExample($type)
    {
        switch ($type) {
            case 'uuid':
                return Text::uuid();

            case 'date':
                return date('Y-m-d');

            case 'datetime':
                return date('Y-m-d H:i:s');

            case 'time':
                return date('H:i:s');

            case 'string':
                return 'Lorem ipsum';

            case 'text':
                return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';

            case 'boolean':
                return true;

            case 'integer':
                return random_int(0, 100000);

            case 'decimal':
                return random_int(0, 100000) . '.' . random_int(0, 100);
        }

        return '';
    }

    /**
     * Generates and returns swagger definition (model) annotation.
     *
     * It uses current table's column definitions to construct a list
     * of required columns and uses properties argument to generate
     * definition annotation.
     *
     * @param  string $properties Swagger properties annotations
     * @return string
     */
    protected function _getDefinition($properties)
    {
        $result = null;
        $table = TableRegistry::get($this->_className);

        $entity = $table->newEntity();
        $hiddenProperties = $entity->hiddenProperties();
        try {
            $columns = $table->schema()->columns();
            $columns = array_diff($columns, $hiddenProperties);
        } catch (Exception $e) {
            return $result;
        }

        $required = [];
        foreach ($columns as $column) {
            $data = $table->schema()->column($column);
            if ($data['null']) {
                continue;
            }
            $required[] = $column;
        }

        $placeholders = [
            '{{definition}}' => Inflector::singularize($this->_className),
            '{{required}}' => implode(',', $required),
            '{{properties}}' => (string)$properties
        ];

        $result = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->_annotations['definition']
        );

        return $result;
    }

    /**
     * Generates and returns swagger paths (controller) annotation.
     *
     * It uses current table's column definitions to construct a list
     * of all visible columns to be used as sorting fields and generates
     * paths annotations on the fly.
     *
     * @return array
     */
    protected function _getPaths()
    {
        $result = null;
        $table = TableRegistry::get($this->_className);

        $entity = $table->newEntity();
        $hiddenProperties = $entity->hiddenProperties();
        try {
            $fields = $table->schema()->columns();
            $fields = array_diff($fields, $hiddenProperties);
            sort($fields);
        } catch (Exception $e) {
            return $result;
        }

        $placeholders = [
            '{{module_human_singular}}' => Inflector::singularize(Inflector::humanize(Inflector::underscore($this->_className))),
            '{{module_human_plural}}' => Inflector::pluralize(Inflector::humanize(Inflector::underscore($this->_className))),
            '{{module_singular}}' => Inflector::singularize($this->_className),
            '{{module_url}}' => Inflector::dasherize($this->_className),
            '{{sort_fields}}' => '"' . implode('", "', $fields) . '"'
        ];

        $result = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->_annotations['paths']
        );

        return $result;
    }
}
