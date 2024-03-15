<?php

namespace App\Admin\Repositories;

use App\Models\MemberWork as Model;
use Dcat\Admin\Repositories\EloquentRepository;
use Carbon\Carbon;

class MemberWork extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;

    /**
     * 创建一条工作记录
     *
     * @param int    $adminUserId
     * @param string $adminUserName
     * @param float  $hourlyRate
     * @return Model
     */
    public function createWorkRecord($adminUserId, $adminUserName, $hourlyRate)
    {
        $workRecord = $this->createModel([
            'admin_user_id' => $adminUserId,
            'admin_user_name' => $adminUserName,
            'work_date' => date('Y-m-d'),
            'work_start_time' => date('Y-m-d H:i:s'),
            'hourly_rate' => $hourlyRate,
        ]);

        $workRecord->save();

        return $workRecord;
    }

    public function find($id)
    {
        return $this->newModel()->find($id);
    }


    /**
     * 更新最后一条工作记录的下班时间、总工时和工资
     *
     * @param int    $adminUserId
     * @param string $endTime
     * @return Model|null
     */
    public function updateLastWorkRecord($adminUserId, $endTime)
    {
        $workRecord = $this->getLastWorkRecord($adminUserId);

        if ($workRecord) {
            $workRecord->work_end_time = $endTime;
            $startTime = Carbon::parse($workRecord->work_start_time);
            $endTime = Carbon::parse($workRecord->work_end_time);
            $diffInMinutes = $startTime->diffInMinutes($endTime, false);
            $workRecord->total_hours = ceil($diffInMinutes / 50); //節數 按照每50分钟为一个单位进行向上取整
            $workRecord->record_salary = $workRecord->total_hours * $workRecord->hourly_rate;
            $workRecord->save();
        }

        return $workRecord;
    }

    /**
     * 获取最后一条工作记录
     *
     * @param int $adminUserId
     * @return Model|null
     */
    public function getLastWorkRecord($adminUserId)
    {
        return $this->model->where('admin_user_id', $adminUserId)
            ->whereNull('work_end_time')
            ->orderBy('created_at', 'desc')
            ->first();
    }
    
}
