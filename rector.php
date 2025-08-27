<?php

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/Entity',
    ]);

    $rectorConfig->sets([
        \Rector\Doctrine\Set\DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        \Rector\Symfony\Set\SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        \Rector\Symfony\Set\SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        new AnnotationToAttribute(\Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity::class),
        new AnnotationToAttribute(\Ibericode\Vat\Bundle\Validator\Constraints\VatNumber::class),
    ]);
};
