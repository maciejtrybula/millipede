<?php
/**
 * @package Millipede\Models
 * @author Maciej Trybuła <mtrybula@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Millipede\Services;

use Millipede\Api\Model\MillipedeInterface as MillipedeModel;
use Millipede\Api\Services\MillipedeInterface as MillipedeService;

/**
 * Class Millipede
 */
class Millipede implements MillipedeService
{
    /**
     * @param array $data
     * @param string $returnType
     *
     * @return string
     */
    public function getMillipede(array $data, $returnType = MillipedeService::RETURN_TYPE_JSON): string
    {
        $availableDevelopers = $this->resolveDevelopers($data);

        $millipede = [];
        $amount = count($availableDevelopers);

        for ($i = 0; $i < $amount; $i++) {
            if (empty($millipede)) {
                $numberPicked = array_rand($availableDevelopers, 1);
                $millipede[] = $availableDevelopers[$numberPicked];
                unset($availableDevelopers[$numberPicked]);

                continue;
            }

            $numberPicked = array_rand($availableDevelopers, 1);
            $chosen = $availableDevelopers[$numberPicked];
            $previousPicked = $i - 1;
            $previousDeveloper = $millipede[$previousPicked];

            if ($chosen[MillipedeModel::PROJECT] === $previousDeveloper[MillipedeModel::PROJECT]) {
                if ($previousDeveloper[MillipedeModel::FUNCTION] === MillipedeModel::FUNCTION_LEADER ||
                    $chosen[MillipedeModel::FUNCTION] === MillipedeModel::FUNCTION_LEADER) {
                    $i--;

                    continue;
                }
            }

            $millipede[] = $chosen;

            unset($availableDevelopers[$numberPicked]);
        }

        if ($returnType === MillipedeService::RETURN_TYPE_JSON) {
            return json_encode($millipede);
        } elseif (MillipedeService::RETURN_TYPE_STRINGIFIED_EMAILS === $returnType) {
            return implode(' <= ', array_column($millipede, 'email'));
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function resolveDevelopers(array $data): array
    {
        $developersAmount = $this->countDevelopers($data);

        $developers = [];

        for ($i = 1; $i <= $developersAmount; $i++) {
            $developers[$i] = [
                MillipedeModel::EMAIL => $data[MillipedeService::MILLIPEDE_EMAIL_PATTERN . $i],
                MillipedeModel::PROJECT => $data[MillipedeService::MILLIPEDE_PROJECT_PATTERN . $i],
                MillipedeModel::FUNCTION => $data[MillipedeService::MILLIPEDE_FUNCTION_PATTERN . $i],

            ];
        }

        return $developers;
    }

    /**
     * @param array $data
     *
     * @return int
     */
    protected function countDevelopers(array $data): int
    {
        $amount = 0;

        foreach ($data as $key => $value) {
            strpos($key, MillipedeModel::EMAIL . '_') !== false ? $amount++ : null;
        }

        return $amount;
    }
}