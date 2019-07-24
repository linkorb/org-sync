<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Denormalizer;

use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;

final class AssociativeArrayDenormalizer extends ArrayDenormalizer
{
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (substr($class, -2) === '[]'
            && isset($context['collection_id_name_map'][substr($class, 0,  -2)])
        ) {
            $newData = [];
            foreach ($data as $key => $item) {
                $item[$context['collection_id_name_map'][substr($class, 0,  -2)]] = $key;
                $newData[] = $item;
            }

            $data = $newData;
        }

        return parent::denormalize($data, $class, $format, $context);
    }
}