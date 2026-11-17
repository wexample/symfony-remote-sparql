<?php

namespace Wexample\SymfonyRemoteSparql\Helper;

use DateTimeImmutable;

/**
 * Values of a SPARQL row, read as PHP ones.
 */
class SparqlValueHelper
{
    /**
     * An `xsd:dateTime` (`2017-05-14T00:00:00Z`) as a day; null when absent, or
     * when the endpoint says it is unknown — Wikidata gives a blank node then,
     * not a date.
     */
    public static function toDate(?string $value): ?DateTimeImmutable
    {
        return null !== $value && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)
            ? new DateTimeImmutable(substr($value, 0, 10))
            : null;
    }
}
