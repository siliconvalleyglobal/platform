<?php declare(strict_types=1);

namespace Shopware\Core\Content\Catalog\DataAbstractionLayer;

use Shopware\Core\Content\Catalog\CatalogDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\FieldException\InvalidFieldException;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Flag\Required;
use Shopware\Core\Framework\Struct\Uuid;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class CatalogField extends FkField
{
    public function __construct()
    {
        parent::__construct('catalog_id', 'catalogId', CatalogDefinition::class);

        $this->setFlags(new Required());
    }

    public function getExtractPriority(): int
    {
        return 1000;
    }

    /**
     * {@inheritdoc}
     */
    protected function invoke(EntityExistence $existence, KeyValuePair $data): \Generator
    {
        if ($this->writeContext->has($this->definition, 'catalogId')) {
            $value = $this->writeContext->get($this->definition, 'catalogId');
        } elseif (!empty($data->getValue())) {
            $value = $data->getValue();
        } else {
            $value = Defaults::CATALOG;
        }

        $restriction = $this->writeContext->getContext()->getCatalogIds();

        //user has restricted catalog access
        if (\is_array($restriction)) {
            $this->validateCatalog($restriction, $value);
        }

        //write catalog id of current object to write context
        $this->writeContext->set($this->definition, 'catalogId', $value);
        if ($this->definition::getTranslationDefinitionClass()) {
            $this->writeContext->set($this->definition::getTranslationDefinitionClass(), 'catalogId', $value);
        }

        yield $this->storageName => Uuid::fromStringToBytes($value);
        yield 'catalog_tenant_id' => Uuid::fromStringToBytes($this->writeContext->getContext()->getTenantId());
    }

    private function validateCatalog(array $restrictedCatalogs, $catalogId): void
    {
        $violationList = new ConstraintViolationList();
        $violations = $this->validator->validate($catalogId, [new Choice(['choices' => $restrictedCatalogs])]);

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $violationList->add(
                new ConstraintViolation(
                    sprintf('No access to catalog id: %s', $catalogId),
                    'No access to catalog id: {{ value }}',
                    $violation->getParameters(),
                    $violation->getRoot(),
                    'catalogId',
                    $violation->getInvalidValue(),
                    $violation->getPlural(),
                    $violation->getCode(),
                    $violation->getConstraint(),
                    $violation->getCause()
                )
            );
        }

        if (\count($violationList)) {
            throw new InvalidFieldException($this->path . '/catalogId', $violationList);
        }
    }
}