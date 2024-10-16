<?php
namespace Tokenmapper\Render\OysterMenu;

require_once __DIR__ . '/../../../support/lib/vendor/autoload.php';

use Approach\Render\Attribute;
use Approach\Render\HTML;
use Approach\Render\Node;
use Stringable;

/*
 * Header
 *
 * Defines a header section for the oyster that is used to hold the dynamic pearl menu and the signout button
 * This inherits from the HTML class and has various checks and so for the content and classes
 *
 * @param array|null $crumbs - an array of strings that represent the breadcrumbs for the header
 * @param string|null $id - the id of the header
 * @param string|array|Node|Attribute|null $classes - the classes of the header
 * @param array|Attribute|null $attributes - the attributes of the header
 * @param string|Stringable|Stream|null $content - the content of the header
 * @param array $styles - the styles of the header
 * @param bool $prerender - whether or not to prerender the header
 * @param bool $selfContained - whether or not the header is self contained
 *
 * @see HTML
 *
 * @return Header
 */
class Header extends HTML
{
    public function __construct(
        public null|array $crumbs = null,
        public null|string|Stringable $id = null,
        null|string|array|Node|Attribute $classes = null,
        public null|array|Attribute $attributes = new Attribute,
        public $content = null,
        public array $styles = [],
        public bool $prerender = false,
        public bool $selfContained = false,
    ) {
        // add .visual class to the classes array
        // make sure classes is not null
        if ($classes === null) {
            $classes = [];
        }

        // The standard html for the header section
        $classes = array_merge($classes, [' header']);

        $headerSection = new HTML(tag: 'section', classes: ['header']);

        $headerSection[] = $backBtn = new HTML(tag: 'button', classes: ['backBtn']);

        $backBtn[] = new HTML(tag: 'i', classes: ['expand ', 'fa ', 'fa-angle-left']);
        $headerSection[] = $menuBtn = new HTML(tag: 'button', classes: ['btn ', 'btn-secondary ', 'current-state ', 'ms-2 ', 'animate__animated ', 'animate__slideInDown'], attributes: ['id' => 'menuButton']);

        $menuBtn[] = new HTML(tag: 'span', attributes: ['id' => 'menuButtonText'], content: 'Location');
        $menuBtn[] = new HTML(tag: 'i', classes: ['fa ', 'fa-caret-down']);

        $headerSection[] = new HTML(tag: 'ul', classes: ['breadcrumbs'], attributes: ['style' => 'display: none']);

        $toolBar = new HTML(tag: 'ul', classes: ['Toolbar']);
        $signoutContent = <<<HTML
            <button>
                <p>
                    <i class="bi bi-box-arrow-right"></i> SignOut
                </p>
            </button>
            HTML;
        $toolBar[] = $menuBtn;
        $toolBar[] = new HTML(tag: 'div', classes: ['signOut'], content: $signoutContent);

        parent::__construct(
            tag: 'section',
            id: $id,
            classes: $classes,
            attributes: $attributes,
            content: $toolBar . $content,
            styles: $styles,
            prerender: $prerender,
            selfContained: $selfContained
        );
    }
}
