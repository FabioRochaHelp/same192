<?php

declare(strict_types=1);

namespace App\Domain\Operations\Actions;

use App\Domain\Operations\DTOs\AdvanceDispatchStageDTO;
use App\Domain\Operations\Enums\DispatchStage;
use App\Domain\Operations\Events\DispatchStageAdvanced;
use App\Domain\Operations\Services\IncidentTimelineRecorder;
use App\Models\Incident;
use App\Models\IncidentDispatch;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class AdvanceDispatchStageAction
{
    public function __construct(
        private IncidentTimelineRecorder $timeline,
    ) {}

    public function execute(AdvanceDispatchStageDTO $dto): IncidentDispatch
    {
        return DB::transaction(function () use ($dto): IncidentDispatch {
            /** @var IncidentDispatch $dispatch */
            $dispatch = IncidentDispatch::query()
                ->whereNull('deleted_at')
                ->findOrFail($dto->incidentDispatchId);

            $current = $dispatch->stage;
            $target = $dto->targetStage;

            if ($current === $target) {
                return $dispatch;
            }

            $ordered = DispatchStage::ordered();
            $ci = $current->index();
            $ti = $target->index();

            if ($ti !== $ci + 1) {
                throw new RuntimeException('Transição de etapa inválida: progressão sequencial obrigatória.');
            }

            $dispatch->update(['stage' => $target]);

            /** @var Incident $incident */
            $incident = $dispatch->incident()->firstOrFail();

            $now = now();
            $payload = ['stage' => $target->value, 'operator_user_id' => $dto->operatorUserId];

            match ($target) {
                DispatchStage::Dispatched => $incident->dispatched_at = $now,
                DispatchStage::DepartedBase => $incident->departed_base_at = $now,
                DispatchStage::ArrivedScene => $incident->arrived_scene_at = $now,
                DispatchStage::LeftScene => $incident->left_scene_at = $now,
                DispatchStage::ArrivedHospital => $incident->arrived_hospital_at = $now,
                DispatchStage::ReleasedHospital => $incident->released_hospital_at = $now,
            };

            $incident->save();

            $this->timeline->record($incident, 'dispatch_stage_advanced', $payload);

            DispatchStageAdvanced::dispatch($incident->fresh(), $dispatch->fresh());

            return $dispatch->fresh();
        });
    }
}
