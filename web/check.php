<?php
require_once dirname(__FILE__).'/../src/CCVRequirements.php';
if (!isset($_SERVER['HTTP_HOST'])) {
    exit('This script cannot be run from the CLI. Run it from a browser.');
}
$ccvRequirements = new CCVRequirements();

$majorProblems = $ccvRequirements->getFailedRequirements();
$minorProblems = $ccvRequirements->getFailedRecommendations();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="robots" content="noindex,nofollow" />
        <title>CCV Checks</title>
        <link rel="stylesheet" href="assets/css/bootstrap.min.css" media="all" />
    </head>
    <body>
    	<nav class="navbar navbar-default" role="navigation">
		  <div class="navbar-header">
		    <a class="navbar-brand">CCV</a>
		  </div>
		</nav>
        <div class="container">
            <h1>Welcome!</h1>
            <p>Welcome to your new CCV installation!</p>
            <p>Please make sure you meet the following requirements.</p>

            <?php if (count($majorProblems)): ?>
                <h2>Major problems</h2>
                <p>Major problems have been detected and <strong>must</strong> be fixed before continuing:</p>
                <ol>
                    <?php foreach ($majorProblems as $problem): ?>
                        <li><?php echo $problem->getHelpHtml() ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>

            <?php if (count($minorProblems)): ?>
                <h2>Recommendations</h2>
                <p>
                    <?php if (count($majorProblems)): ?>Additionally, to<?php else: ?>To<?php endif; ?> enhance your CCV experience,
                    it’s recommended that you fix the following:
                </p>
                <ol>
                    <?php foreach ($minorProblems as $problem): ?>
                        <li><?php echo $problem->getHelpHtml() ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>

            <?php if ($ccvRequirements->hasPhpIniConfigIssue()): ?>
                <p>
                    <?php if ($ccvRequirements->getPhpIniConfigPath()): ?>
                        Changes to the <strong>php.ini</strong> file must be done in "<strong><?php echo $ccvRequirements->getPhpIniConfigPath() ?></strong>".
                    <?php else: ?>
                        To change settings, create a "<strong>php.ini</strong>".
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if (!count($majorProblems) && !count($minorProblems)): ?>
                <p>Your configuration looks good to run CCV.</p>
            <?php endif; ?>

            <?php if (!count($majorProblems)): ?>
            	<div class="alert alert-success">
            		You can now proceed to the next step in the installation.
            	</div>
            	<div class="alert alert-danger">
            		<strong>Security danger!</strong> It is recommended to remove this file after you have completed the installation.
            	</div>
            <?php endif; ?>
            <?php if (count($majorProblems) || count($minorProblems)): ?>
                <a href="check.php" class="btn btn-primary">Re-check configuration</a>
            <?php endif; ?>
		</div>
    </body>
</html>