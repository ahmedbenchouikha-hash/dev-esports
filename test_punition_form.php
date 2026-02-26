<?php
// Quick test to see what choices are available in PunitionType form

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/config/bootstrap.php';

use App\Entity\Punition;
use App\Form\PunitionType;
use App\Enum\StatutPunition;
use Symfony\Component\Form\FormFactoryInterface;

// Get the form factory from the container
$container = require __DIR__.'/config/bootstrap.php';
$formFactory = $container->get('form.factory');

// Create the form
$punition = new Punition();
$form = $formFactory->create(PunitionType::class, $punition);

// Check the playerStatus field
$playerStatusField = $form->get('playerStatus');
$choices = $playerStatusField->getConfig()->getOptions()['choices'];

echo "=== StatutPunition::choices() ===\n";
var_dump(StatutPunition::choices());

echo "\n=== Form field choices ===\n";
var_dump($choices);

echo "\n=== Form view choices (var_choices) ===\n";
var_dump($playerStatusField->createView()->vars['choices']);
