<?php
namespace App\Swagger;

use Cake\Core\App;
use Cake\Database\Exception;
use Cake\I18n\Time;
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
    protected $content = null;

    /**
     * Supported property types.
     *
     * @var array
     */
    protected $supportedTypes = ['uuid', 'string', 'text', 'boolean', 'datetime', 'date', 'time', 'decimal', 'integer'];

    /**
     * Class name to generate annotations for.
     *
     * @var string
     */
    protected $className = '';

    /**
     * Full path name of the file to generate annotations for.
     *
     * @var string
     */
    protected $path = '';

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
    protected $annotations = [
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
        $this->className = $className;

        $this->path = $path;
        $this->withInfo = $withInfo;
    }

    /**
     * Swagger annotation content getter.
     *
     * @return string
     */
    public function getContent()
    {
        if (empty($this->content)) {
            $this->generateContent();
        }

        return $this->content;
    }

    /**
     * Swagger annotation content setter.
     *
     * @param string $content The content
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Method that generates and sets swagger annotation content.
     *
     * @return void
     */
    protected function generateContent()
    {
        $result = file_get_contents($this->path);

        $info = $this->getInfo();

        $properties = $this->getProperties();

        $definition = $this->getDefinition($properties);

        $paths = $this->getPaths();

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
            $this->annotations['info']
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
    protected function getProperties()
    {
        $table = TableRegistry::get($this->className);

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
                $this->annotations['property']
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
        $type = in_array($type, $this->supportedTypes) ? $type : static::DEFAULT_TYPE;

        $result = [];
        switch ($type) {
            case 'uuid':
                $result = [
                    'type' => 'string',
                    'format' => 'uuid',
                    'example' => Text::uuid()
                ];
                break;

            case 'string':
                $result = [
                    'type' => 'string',
                    'format' => 'string',
                    'example' => 'Lorem ipsum'
                ];
                break;

            case 'text':
                $result = [
                    'type' => 'string',
                    'format' => 'string',
                    'example' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod ' .
                        'tempor incididunt ut labore et dolore magna aliqua.'
                ];
                break;

            case 'boolean':
                $result = [
                    'type' => 'boolean',
                    'format' => 'boolean',
                    'example' => true
                ];
                break;

            case 'datetime':
                $result = [
                    'type' => 'string',
                    'format' => 'date-time',
                    'example' => Time::now()->i18nFormat('yyyy-MM-dd HH:mm:ss')
                ];
                break;

            case 'date':
                $result = [
                    'type' => 'string',
                    'format' => 'date',
                    'example' => Time::now()->i18nFormat('yyyy-MM-dd')
                ];
                break;

            case 'time':
                $result = [
                    'type' => 'string',
                    'format' => 'time',
                    'example' => Time::now()->i18nFormat('HH:mm:ss')
                ];
                break;

            case 'decimal':
                $result = [
                    'type' => 'number',
                    'format' => 'float',
                    'example' => (mt_rand() / mt_getrandmax()) * (mt_getrandmax() / 1000)
                ];
                break;

            case 'integer':
                $result = [
                    'type' => 'integer',
                    'format' => 'integer',
                    'example' => mt_rand()
                ];
                break;
        }

        return $result;
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
    protected function getDefinition($properties)
    {
        $result = null;
        $table = TableRegistry::get($this->className);

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
            '{{definition}}' => Inflector::singularize($this->className),
            '{{required}}' => implode(',', $required),
            '{{properties}}' => (string)$properties
        ];

        $result = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->annotations['definition']
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
    protected function getPaths()
    {
        $result = null;
        $table = TableRegistry::get($this->className);

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
            '{{module_human_singular}}' => Inflector::singularize(Inflector::humanize(Inflector::underscore($this->className))),
            '{{module_human_plural}}' => Inflector::pluralize(Inflector::humanize(Inflector::underscore($this->className))),
            '{{module_singular}}' => Inflector::singularize($this->className),
            '{{module_url}}' => Inflector::dasherize($this->className),
            '{{sort_fields}}' => '"' . implode('", "', $fields) . '"'
        ];

        $result = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->annotations['paths']
        );

        return $result;
    }
}
