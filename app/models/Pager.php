<?php

namespace Core;

defined('ROOT') or die ("Direct script access denied");

/**
* Pager Class
*/
class Pager
{

	public $links 			= [];
	public $limit 			= 10;
	public $offset 		= 0;
	public $start 			= 1;
	public $end 			= 1;
	public $page_number 	= 1;

	/**
   * Constructor to initialize the pager with a specified limit and extras.
   *
   * @param int $limit Number of items per page.
   * @param int $extras Number of extra pages to show before and after the current page.
   */
	public function __construct(int $limit = 10, $extras = 1)
	{
		/* Determine the current page number from the URL, default to 1 of not set */
		$page_number = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
		$page_number = $page_number < 1 ? 1 : $page_number;

		/* Calculate the start and end page numbers for the pagination links */
		$this->start = $page_number - $extras;
		$this->end = $page_number + $extras;
		$this->start = $this->start < 1 ? 1 : $this->start;

		/* Set the current page number and calculate the offset */
		$this->page_number = $page_number;
		$this->offset = ($page_number - 1) * $limit;

		/* Construct the current URL */
		$url = $_GET['url'] ?? 'home';
		$query_string = str_replace("url=", "", $_SERVER['QUERY_STRING']);
		$current_link = ROOT . '/'.$url . '?' . trim(str_replace($url, "", $query_string), '&');
		if (!strstr($current_link, "page="))
			$current_link .= '&page='.$page_number;

		/* Create links for the first page and the next page */
		$first_link = preg_replace("/page=[0-9]+/", "page=1", $current_link);
		$next_link = preg_replace("/page=[0-9]+/", "page=".($page_number + $extras + 1), $current_link);

		/* Store the links in the links array */
		$this->links['current'] 	= $current_link;
		$this->links['first'] 		= $first_link;
		$this->links['next'] 		= $next_link;

	}

	/**
   * Display pagination links using Bootstrap CSS.
   */
	public function displayBootstrapCSS()
	{
		?>
		<nav aria-label="Page navigation example">
			<ul class="pagination">
				<li class="page-item"><a class="page-link" href="<?=$this->links['first']?>">First</a></li>

				<?php for($x = $this->start;$x <= $this->end;$x++):?>
					<li class="page-item <?=$x == $this->page_number ? 'active':''?>">
						<a class="page-link" href="<?=preg_replace("/page=[0-9]+/", "page=".$x, $this->links['current'])?>"><?=$x?></a>
					</li>
				<?php endfor?>

				<li class="page-item"><a class="page-link" href="<?=$this->links['next']?>">Next</a></li>
			</ul>
		</nav>
		<?php
	}

	public function displayTailwindCSS()
	{
		?>
		<nav aria-label="Page navigation example">
			<ul class="pagination">
				<li class="page-item"><a class="page-link" href="<?=$this->links['first']?>">First</a></li>

				<?php for($x = $this->start;$x <= $this->end;$x++):?>
					<li class="page-item <?=$x == $this->page_number ? 'active':''?>">
						<a class="page-link" href="<?=preg_replace("/page=[0-9]+/", "page=".$x, $this->links['current'])?>"><?=$x?></a>
					</li>
				<?php endfor?>

				<li class="page-item"><a class="page-link" href="<?=$this->links['next']?>">Next</a></li>
			</ul>
		</nav>
		<?php
	}

	public function displayCustomCSS()
	{
		?>
		<nav aria-label="Page navigation example">
			<ul class="pagination">
				<li class="page-item"><a class="page-link" href="<?=$this->links['first']?>">First</a></li>

				<?php for($x = $this->start;$x <= $this->end;$x++):?>
					<li class="page-item <?=$x == $this->page_number ? 'active':''?>">
						<a class="page-link" href="<?=preg_replace("/page=[0-9]+/", "page=".$x, $this->links['current'])?>"><?=$x?></a>
					</li>
				<?php endfor?>

				<li class="page-item"><a class="page-link" href="<?=$this->links['next']?>">Next</a></li>
			</ul>
		</nav>
		<?php
	}
}