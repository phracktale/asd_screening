<?php

declare(strict_types=1);

namespace TsaRepere\Assessment;

/**
 * Vocabulaire des reponses differentielles (section F du catalogue).
 *
 * "no" a ete remplace par des valeurs non ambigues (specification 6.9, review P0-2).
 * Une condition differentielle peut etre concomitante au TSA : un diagnostic pose
 * ne doit donc jamais annuler automatiquement la suspicion de TSA.
 */
enum DifferentialStatus: string
{
    case Diagnosed = 'diagnosed';
    case Suspected = 'suspected';
    case UnderEvaluation = 'under_evaluation';
    case RuledOut = 'ruled_out';
    case NotReported = 'not_reported';
    case Unknown = 'unknown';

    /** Statut representant une piste differentielle non tranchee. */
    public function isUnresolved(): bool
    {
        return $this === self::UnderEvaluation;
    }

    /** Statut evoquant une condition possible mais non confirmee. */
    public function isSuspected(): bool
    {
        return $this === self::Suspected;
    }
}
