<?php

namespace App\Repositories;

use App\Helpers\ImageHelper;

class MainRepository
{
    protected $model;
    protected $fileFolder;
    protected $files;

    public function clearRequest($data)
    {
        foreach ($data as $key => $value) {
            $trimmedValue = (gettype($value) == "string") ? trim($value) : $value;
            if (empty($trimmedValue) && $trimmedValue != 0) {
                unset($data[$key]);
                continue;
            }
            $data[$key] = $trimmedValue;
            if ($this->files != null && in_array($key, $this->files)) {
                $data[$key] = ImageHelper::upload($data[$key], $this->fileFolder);
            }
            if ($key == "password") {
                $data[$key] = bcrypt($value);
            }
        }
        return $data;
    }

    public function getDataTable(array $with = null)
    {
        // لو مفيش علاقات مبعوتة، رجع الكويري عادي من غير eager loading
        if (is_null($with)) {
            return $this->model->query()->latest();
        }

        // لو فيه مصفوفة، نفذ الكويري مع العلاقات
        return $this->model->query()->latest()->with($with);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function sum($column, $where = null)
    {
        $query = $this->model->newQuery();

        if (is_array($where)) {
            foreach ($where as $col => $value) {
                $query->where($col, $value);
            }
        }

        if ($where instanceof \Closure) {
            $where($query);
        }

        return $query->sum($column);
    }

    public function first()
    {
        return $this->model->first();
    }

    /**
     * Row count for the model, honouring its global scopes (SoftDeletes etc).
     * Takes an optional array of where-conditions.
     */
    public function count(array $where = [])
    {
        $query = $this->model->newQuery();

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        return $query->count();
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function store($data)
    {
        $data = $this->clearRequest($data);
        return $this->model->create($data);
    }

    public function update($id, $data)
    {
        $data = $this->clearRequest($data);
        return $this->model->find($id)->update($data);
    }

    public function get()
    {
        return $this->model->get();
    }

    public function getWhere($where)
    {
        return $this->model->where($where)->get();
    }

    public function getLatestWhere($where)
    {
        return $this->model->where($where)->latest()->get();
    }

    public function getWhereFirst($where)
    {
        return $this->model->where($where)->first();
    }

    public function getWhereWithLimit($where, $num)
    {
        return $this->model->where($where)->limit($num)->get();
    }

    public function getWhereWithoutGet($where)
    {
        return $this->model->where($where);
    }

    public function getWhereWithoutGetAndOrderBy($where, $orderBy)
    {
        return $this->model->where($where)->orderBy('id', $orderBy);
    }

    public function getWhereIn($column, $where)
    {
        return $this->model->whereIn($column, $where)->get();
    }

    public function getWhereInWithoutGet($column, $where)
    {
        return $this->model->whereIn($column, $where);
    }

    public function lastId()
    {
        $row = $this->model->orderBy('id', 'DESC')->first();
        return (isset($row->id)) ? ($row->id + 1) : 1;
    }


    public function deleteWithFile($id, $column)
    {
        $obj = $this->find($id);
        deleteFile($obj->{$column});
        $this->delete($id);
    }

    public function updateWhere($where, $data)
    {
        return $this->model->where($where)->update($data);
    }

    public function findToArray($id)
    {
        return $this->model->find($id)->toArray();
    }

    public function deleteWhere($where)
    {
        return $this->model->where($where)->delete();
    }

    public function UpdateOrStore($where, $data)
    {
        return $this->model->updateOrCreate($where, $data);
    }

    public function getModel()
    {
        return $this->model;
    }
}
