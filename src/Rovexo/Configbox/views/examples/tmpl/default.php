<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewExamples */
?>
<div <?php echo $this->getViewAttributes();?>>

	<div class="demo-buttons">
		<a class="btn btn-default trigger-load-json">Example of using server.makeRequest</a>
		<a class="btn btn-default trigger-inject-view">Example of using server.injectHtml</a>
	</div>

	<div class="target-examples1-view"></div>

	<h1>Example data</h1>
	<p>In the backend you can open a list of example data. Controller name is adminexamples.</p>
	<pre class="examples">
		<?php var_dump($this->examples);?>
	</pre>


</div>

