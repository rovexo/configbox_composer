<?php
defined('CB_VALID_ENTRY') or die();
/** @var $this ConfigboxViewAdmindashboard */
?>

<div <?php echo $this->getViewAttributes();?>
		data-license-key="<?php echo hsc($this->licenseKey);?>"
		data-configbox-version="<?php echo hsc($this->configboxVersion);?>"
		data-endpoint-url-dashboard-info="<?php echo hsc($this->urlEndPointDashboardInfo);?>"
		data-endpoint-url-license-info="<?php echo hsc($this->urlEndPointLicenseInfo);?>">

	<div class="row">


		<div class="col-sm-6">

			<?php if (count($this->criticalIssues)) { ?>

				<div class="box issues critical-issues">
					<h2><?php echo KText::_('Critical Issues');?></h2>
					<ul class="issue-list">
						<?php foreach ($this->criticalIssues as $issue) { ?>
							<li class="issue-item toggle-wrapper">
								<h3 class="issue-title toggle-handle"><span></span><?php echo hsc($issue->title);?></h3>
								<div class="issue-details toggle-content">
									<h4><?php echo KText::_('Problem');?></h4>
									<div class="issue-problem"><?php echo hsc($issue->problem);?></div>
									<h4><?php echo KText::_('Solution');?></h4>
									<div class="issue-solution"><?php echo $issue->solution;?></div>
									<h4><?php echo KText::_('Who can fix it');?></h4>
									<div class="issue-access"><?php echo hsc($issue->access);?></div>

									<?php if (!empty($issue->isStructureFail)) { ?>
										<a class="btn btn-primary trigger-remove-file-structure-warning"><?php echo KText::_('Is fixed');?></a>
									<?php } ?>
								</div>
							</li>
						<?php } ?>
					</ul>
				</div>

			<?php } ?>

			<div class="issues box">
				<h2><?php echo KText::_('Health Check');?></h2>
				<?php if (count($this->issues)) { ?>
					<ul class="issue-list">
						<?php foreach ($this->issues as $issue) { ?>
							<li class="issue-item toggle-wrapper">
								<h3 class="issue-title toggle-handle"><span></span><?php echo hsc($issue->title);?></h3>
								<div class="issue-details toggle-content">
									<h4><?php echo KText::_('Problem');?></h4>
									<div class="issue-problem"><?php echo hsc($issue->problem);?></div>
									<h4><?php echo KText::_('Solution');?></h4>
									<div class="issue-solution"><?php echo hsc($issue->solution);?></div>
									<h4><?php echo KText::_('Who can fix it');?></h4>
									<div class="issue-access"><?php echo hsc($issue->access);?></div>
								</div>
							</li>
						<?php } ?>
					</ul>
				<?php } else { ?>
					<div class="no-issues"><span class="fa fa-check-square fa-lg"></span><?php echo KText::_('No issues found.')?></div>
				<?php } ?>
			</div>

			<div class="tips box">
				<h2><?php echo KText::_('Suggestions to improve performance');?></h2>
				<?php if (count($this->performanceTips)) { ?>
					<ul class="tip-list">
						<?php foreach ($this->performanceTips as $tip) { ?>
							<li class="tip-item toggle-wrapper">
								<h3 class="k-tip-title toggle-handle"><span></span><?php echo hsc($tip->title);?></h3>
								<div class="tip-details toggle-content">
									<h4><?php echo KText::_('Prospect');?></h4>
									<div class="tip-prospect"><?php echo hsc($tip->prospect);?></div>
									<h4><?php echo KText::_('Solution');?></h4>
									<div class="tip-solution"><?php echo hsc($tip->solution);?></div>
									<h4><?php echo KText::_('Who can set it up');?></h4>
									<div class="tip-access"><?php echo hsc($tip->access);?></div>
								</div>
							</li>
						<?php } ?>
					</ul>
				<?php } else { ?>
					<div class="no-tips"><span class="fa fa-check-square fa-lg"></span><?php echo KText::_('You implemented all suggested performance tips.')?></div>
				<?php } ?>
			</div>

			<div class="software-update box">
				<h2><?php echo KText::_('Software Update');?></h2>
				<p class="checking-for-update"><i class="fa fa-spinner fa-spin"></i><span class="text"><?php echo KText::_('Checking for updates');?></span></p>
				<p class="patchlevel-update-available"><span class="fa fa-exclamation-triangle"></span><?php echo KText::sprintf('A minor update to version %s was released. Currently installed version is %s.','<span class="latest-version-patchlevel"></span>', hsc($this->configboxVersion));?> <a class="kenedo-new-tab software-update-link"><?php echo KText::_('Update');?></a></p>
				<p class="major-update-available"><span class="fa fa-exclamation-triangle"></span><?php echo KText::sprintf('A major update to version %s was released. Currently installed version is %s.','<span class="latest-version-major"></span>', hsc($this->configboxVersion))?> <a class="kenedo-new-tab software-update-link"><?php echo KText::_('Update');?></a></p>
				<p class="no-update-available"><span class="fa fa-check-square fa-lg"></span><?php echo KText::sprintf('You are using the latest version of ConfigBox');?> (<?php echo hsc($this->configboxVersion);?>).</p>
			</div>

			<?php if ($this->licenseKey) { ?>

				<div class="license box">
					<h2><?php echo KText::_('DASHBOARD_LICENSE_HEADING');?></h2>
					<div class="license-key">
						<span class="key"><?php echo KText::_('DASHBOARD_LICENSE_LICENSE_KEY');?></span>
						<span class="value"><?php echo hsc($this->licenseKey);?></span>
					</div>

					<div class="wrapper-license-data">
						<i class="fa fa-spinner fa-spin fa-fw"></i>
						<?php echo KText::_('DASHBOARD_LOADING_LICENSE_DATA');?>
					</div>
				</div>

			<?php } ?>

			<div class="manual box">
				<h2><?php echo KText::_('ConfigBox Manual');?></h2>
				<a class="manual-link kenedo-new-tab" href="http://www.configbox.at/en/manual"><i class="fa fa-download"></i><?php echo KText::_('Online Manual');?></a>
			</div>

			<div class="stats box">
				<h2><?php echo KText::_('Current server status');?></h2>
				<?php if (count($this->currentStats)) { ?>
					<ul class="stat-list">
						<?php foreach ($this->currentStats as $stat) { ?>
							<li class="stat-item toggle-wrapper">
								<h3 class="stat-title toggle-handle"><span></span><?php echo KText::sprintf('%s uses %s%%', $stat->title, $stat->percentageUsed);?></h3>
								<div class="stat-values toggle-content">
									<span class="stat-used"><?php echo KText::sprintf('Using %s%s', $stat->used, $stat->unit);?></span>
									<span class="stat-total"><?php echo KText::sprintf('of %s%s', $stat->total, $stat->unit);?></span>
									<span class="stat-free"><?php echo KText::sprintf('- Free space %s%s (%s%%)', $stat->free, $stat->unit, $stat->percentageFree);?></span>
								</div>
							</li>
						<?php } ?>
					</ul>
				<?php } else { ?>
					<div class="no-stats"><?php echo KText::_('There were no relevant status values found.')?></div>
				<?php } ?>
			</div>

		</div>

		<div class="col-sm-6">
			<div class="news">
				<h2><?php echo KText::_('News');?></h2>
				<div class="news-target"><i class="fa fa-spinner fa-spin" style="margin-right:5px"></i><?php echo KText::_('Loading');?></div>
			</div>
			<div class="overflow-gradient"></div>
		</div>

	</div>

	<?php if ($this->showProductTour) { ?>
		<div class="wrapper-product-tour">
			<?php echo $this->tourHtml;?>
		</div>
	<?php } ?>
	
</div>
