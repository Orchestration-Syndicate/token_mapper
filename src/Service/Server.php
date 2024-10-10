<?php

namespace Tokenmapper\Service;

use Approach\Resource\Aspect\Aspect;
use Approach\Service\flow;
use Approach\Service\format;
use Approach\Service\Service;
use Approach\Service\target;
use Approach\deploy;
use Approach\path;
use Approach\Render\HTML;
use Approach\Resource\MariaDB\Aspect\field;
use Approach\Scope;

use Tokenmapper\Render\OysterMenu\Oyster;
use Tokenmapper\Render\OysterMenu\Pearl;
use Tokenmapper\Render\Intent;

use const Approach\Resource\field;

require_once __DIR__ . '/../../support/lib/vendor/autoload.php';

class Server extends Service
{
    public static array $registrar = [];
    protected Scope $scope;

    // List all files in a directory
    static function listFiles($path, $isRoot = true, $stripPHP = false)
    {
        $result = $isRoot ? ['root' => []] : [];
        $items = scandir($path);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                $result[$item] = self::listFiles($fullPath, false, $stripPHP);
            } else {
                if ($stripPHP) {
                    $item = str_replace('.php', '', $item);
                }

                if ($isRoot) {
                    $result['root'][] = $item;
                } else {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }

    // Generates only the base menu of the servers that are available in the resource dir constructed
    // from Scope
    public function MakeServerMenu($context)
    {
        $resource_path = $this->scope->GetPath(path::resource);
        $resource_path = str_replace('//', '/src/', $resource_path);

        $pearls = [];
        $target = $context['_response_target'];
        $path = $resource_path . $context['path'];
        $entries = self::listFiles($path, true, true);

        foreach ($entries as $entry => $data) {
            if ($entry === 'root') {
                continue;
            }

            $visual = new Intent(
                tag: 'div',
                classes: ['control', ' visual', ' component-section'],
                context: ['_response_target' => $target . ' > ul > li[data-pearl="' . $entry . '"]', 'id' => $entry, 'path' => $context['path'] . '/' . $entry],
                intent: ['APPEND' => ['Menu' => 'Child']],
            );

            $visual[] = new HTML('h3', content: $entry);

            $pearl = new Pearl($visual, $entry);
            $pearls[] = $pearl;
        }

        $oyster = new Oyster(pearls: $pearls, classes: ['Toolbar ', ' active']);

        return [
            'APPEND' => [
                $context['_response_target'] => $oyster->render(),
            ],
        ];
    }

    // Makes the actual menu and also refreshes the field content using the context of the current
    // server and the path
    public function MakeMenu($context)
    {
        $resource_path = $this->scope->GetPath(path::resource);
        $resource_path = str_replace('//', '/src/', $resource_path);

        $pearls = [];
        $target = $context['_response_target'];
        $path = $resource_path . $context['path'];
        $entries = self::listFiles($path, true, true);
        $entries = $entries['root'];

        if (count($entries) == 0) {
            $curr_path = str_replace('/', '\\', $context['path']);
            $classname = '\\' . $this->scope->project . '\Resource' .  $curr_path;
            // append Aspect to the path after the first \\
            $aspectpath = explode('\\', $curr_path);
            $temp = $aspectpath[1];
            $temp .= '\\Aspect';
            $aspectpath[1] = $temp;
            $curr_path = implode('\\', $aspectpath);
            $curr_path .= '\\field';

            $aspect = '\\' . $this->scope->project . '\Resource' . $curr_path;
            $aspect = $aspect::_approach_field_profile_[field::label];

            $fields = $classname::GetProfile()[Aspect::field];
            $field_names = [];
            foreach ($fields as $key => $value) {
                $field_names[] = $aspect[$key];
            }

            foreach ($field_names as $field_name) {
                $visual = new Intent(
                    tag: 'div',
                    classes: ['control', ' visual', ' component-section'],
                    context: ['_response_target' => $target, 'id' => $field_name, 'path' => $context['path'] . '/' . $field_name],
                    intent: ['APPEND' => ['Menu' => 'Render']],
                );

                $visual[] = new HTML('h3', content: $field_name);

                $pearl = new Pearl($visual, $field_name);
                $pearls[] = $pearl;

                // TODO: Come up with a better way to recognize that it is server
                // if ($context['path'] != '') {
                $curr_path = str_replace('/', '\\', $context['path']);
                $classname = '\\' . $this->scope->project . '\Resource' . $curr_path;

                $fields = $classname::GetProfile()[Aspect::field];

                $converted = [];
                foreach ($fields as $field => $descriptor) {
                    $cases = field::getProfileProperties();
                    $label = $descriptor[field::label];
                    foreach ($cases as $case => $index) {
                        $converted[$label][$case] = $descriptor[$index];
                    }
                }
                $pearl->attributes['aspect-source'] = $classname;
                $pearl->attributes['aspect-field'] = htmlentities(json_encode($converted[$field_name]));
            }
            // }

            $oyster = new Oyster(pearls: $pearls, classes: ['active']);

            $temp_target = str_replace('"', '\"', $target);

            return [
                'APPEND' => [
                    $context['_response_target'] => $oyster->render(),
                ],
                'TRIGGER' => [
                    'changeMenu( {"selector":"' . $temp_target . '"} )' => []
                ],
            ];
        }

        foreach ($entries as $key => $entry) {
            $visual = new Intent(
                tag: 'div',
                classes: ['control', ' visual', ' component-section'],
                context: ['_response_target' => $target . ' > ul > li[data-pearl="' . $entry . '"]', 'id' => $entry, 'path' => $context['path'] . '/' . $entry],
                intent: ['APPEND' => ['Menu' => 'Child']],
            );

            $visual[] = new HTML('h3', content: $entry);

            $pearl = new Pearl($visual, $entry);

            // skip if Aspects is detected
            if ($entry === 'Aspect') {
                continue;
            }


            $pearls[] = $pearl;
        }

        $oyster = new Oyster(pearls: $pearls, classes: ['active']);

        $temp_target = str_replace('"', '\"', $target);

        return [
            'APPEND' => [
                $context['_response_target'] => $oyster->render(),
            ],
            'TRIGGER' => [
                'changeMenu( {"selector":"' . $temp_target . '"} )' => []
            ]
        ];
    }

    public function SaveMenu($context)
    {
        $menu = $context['Menu'];

        return [
            'REFRESH' => [
                $context['_response_target'] => '<div>Menu Saved with data: ' . json_encode($menu) . '</div>'
            ],
        ];
    }

    public function __construct(
        flow $flow = flow::in,
        bool $auto_dispatch = false,
        ?format $format_in = format::json,
        ?format $format_out = format::json,
        ?target $target_in = target::stream,
        ?target $target_out = target::stream,
        $input = [Service::STDIN],
        $output = [Service::STDOUT],
        mixed $metadata = [],
    ) {
        $path_to_project = __DIR__ . '/../../src/';
        $path_to_approach = __DIR__ . '/../../support/lib/approach/';
        $path_to_support = __DIR__ . '/../../support/';

        $this->scope = new Scope(
            project: 'Tokenmapper',
            path: [
                path::project->value => $path_to_project,
                path::installed->value => $path_to_approach,
                path::support->value => $path_to_support,
            ],
            deployment: [
                deploy::base->value => 'localhost',
                deploy::ensemble->value => 'localhost',
                deploy::resource->value => 'localhost',
                deploy::resource_user->value => 'root',
            ]
        );

        self::$registrar['Menu']['Base'] = function ($context) {
            return $this->MakeServerMenu($context);
        };
        self::$registrar['Menu']['Child'] = function ($context) {
            return $this->MakeMenu($context);
        };
        self::$registrar['Menu']['Save'] = function ($context) {
            return $this->SaveMenu($context);
        };
        parent::__construct($flow, $auto_dispatch, $format_in, $format_out, $target_in, $target_out, $input, $output, $metadata);
    }

    function processIntents($intent): array
    {
        $result = [];
        if (
            is_array($intent) &&
            !isset($intent['support']) &&
            !isset($intent['command'])
        ) {
            foreach ($intent as $i) {
                $predicated_result = $this->processIntent($i);
                $result = array_merge($result, $predicated_result);
            }
            return $result;
        } else {
            return $this->processIntent($intent);
        }
    }

    /**
     * Process a generic intent
     *
     * @param array $intent
     * @return array
     */
    public function processIntent(array $intent): array
    {
        $result = [];
        $context = $intent['support'];
        $command = $intent['command'];
        foreach ($command as $predicate => $action) {
            $scope = key($action);
            $call = $action[$scope];

            if (!isset(self::$registrar[$scope][$call])) {
                $result = [
                    'APPEND' => ['#APPROACH_DEBUG_CONSOLE' => '<br /><p>' . 'Unmatched intent! <br />' . var_export($intent, true) . '</p><br />']
                ];
            } else {
                $result = self::$registrar[$scope][$call]($context);
            }
        }
        return $result;
    }

    public function Process(?array $payload = null): void
    {
        $payload = $payload ?? $this->payload;

        foreach ($payload as $index => $intent) {
            $this->payload[$index] = $this->processIntents($intent);
        }
    }
}
