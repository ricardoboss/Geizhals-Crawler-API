<?php

namespace Geizhals\Crawler;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\AbstractNode;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\NotLoadedException;

class Breadcrumbs
{
	private array $items = [];

	/**
	 * Breadcrumbs constructor.
	 *
	 * @param Dom $dom
	 * @throws ChildNotFoundException|NotLoadedException|CircularException
	 */
	public function load(Dom $dom)
	{
		/** @var Dom\Node\HtmlNode $breadcrumbParent */
		$breadcrumbParent = $dom->find('[itemtype="http://schema.org/BreadcrumbList"]')[0];

		/** @var AbstractNode $node */
		foreach ($breadcrumbParent->getChildren() as $node) {
			if ($node->isTextNode())
				continue;

			/** @var AbstractNode $nameNode */
			$nameNode = $node->find('[itemprop="name"]')->offsetGet(0);

			if ($nameNode->hasAttribute('content'))
				$this->items[] = $nameNode->getAttribute('content');
			else
				$this->items[] = $nameNode->innerText;
		}
	}

	/**
	 * @return array
	 */
	public function getItems(): array
	{
		return $this->items;
	}
}
