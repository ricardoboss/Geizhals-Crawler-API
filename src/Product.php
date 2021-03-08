<?php

namespace Geizhals\Crawler;

use JetBrains\PhpStorm\ArrayShape;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\AbstractNode;
use PHPHtmlParser\Dom\Node\Collection;
use PHPHtmlParser\Dom\Node\HtmlNode;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

class Product
{
	private string $label;

	private array $properties = [];

	/**
	 * @param Dom $dom
	 *
	 * @throws ChildNotFoundException | CircularException | ContentLengthException | LogicalException | StrictException| NotLoadedException
	 */
	public function load(Dom $dom) {
		$this->label = $dom->find('h1.variant__header__headline')[0]->innerText;

		/** @var HtmlNode $grid */
		$grid = $dom->find('.variant__header__specs-grid')[0];

		$gridChildren = $grid->getChildren();

		/** @var string $noscriptArea */
		$noscriptArea = array_pop($gridChildren)->innerHtml;

		$noscriptDom = new Dom();
		$noscriptDom->loadStr("<html><body>$noscriptArea</body></html>");
		$specRows = $noscriptDom->getElementsByClass('variant__header__specs-grid__item');

		/** @var AbstractNode $specRow */
		foreach ($specRows as $specRow) {
			/** @var HtmlNode[]|Collection $spans */
			$spans = $specRow->find('span');
			if ($spans->count() != 2)
				continue;

			$name = trim($spans[0]->text);
			$value = trim($spans[1]->text);

			$this->properties[$name] = $value;
		}
	}

	#[ArrayShape(['label' => "string", 'properties' => "array"])]
	public function asArray(): array {
		return [
			'label' => $this->label,
			'properties' => $this->properties
		];
	}
}
