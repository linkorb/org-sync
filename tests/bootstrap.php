<?php declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegistry;

date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerFile(__DIR__ . "/../vendor/symfony/serializer/Annotation/DiscriminatorMap.php");