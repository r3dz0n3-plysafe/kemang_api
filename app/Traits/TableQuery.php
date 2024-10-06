<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait TableQuery
{
    use ResponseApi;

    /**
     * @param Builder|\Illuminate\Database\Query\Builder|Model $query
     * @param array $columns
     * @param Request $request
     * @param bool $newMapping
     * @return array
     */
    public function queryMethod($query, array $columns, Request $request, bool $newMapping = false)
    {

        $resultCount = $query->get()->count();


        // Filtering
        if ($request->has('filters')) {
            $filters = json_decode($request->get('filters'), true);
            if (count($filters)) {
                foreach ($filters as $column => $filter) {
                    if (isset($filter[0])) {
                        foreach ($filter as $subFilter) {
                            if (!empty($subFilter['value'])) {
                                $this->matchmode($query, $column, $subFilter['matchMode'], $subFilter['value'], $subFilter['operator']);
                            }
                        }
                    } else if (is_array($filter) && !empty($filter['value'])) {
                        $this->matchmode($query, $column, $filter['matchMode'], $filter['value']);
                    }
                }
            }
        }

        // Global Filtering
        if ($request->has('globalFilter') && count($columns)) {
            $globalFilter = json_decode($request->get('globalFilter'), true);
            $query->where(function ($q) use ($columns, $globalFilter) {
                foreach ($columns as $col) {
                    $this->matchmode($q, $col, $globalFilter['matchMode'], $globalFilter['value'], 'or');
                }
            });
        }

        // Apply multi-sort columns
        if ($request->has('multiSortMeta')) {
            $sortColumns = json_decode($request->get('multiSortMeta'), true);
            if (count($sortColumns)) {
                foreach ($sortColumns as $sort) {
                    $query->orderBy($sort['field'], $sort['order'] == 1 ? 'asc' : 'desc');
//                    $query->orderByRaw("LENGTH(".$sort['field'].") " . ($sort['order'] == 1 ? 'asc' : 'desc'));
                }
            }
        } else if ($request->has('sortField') && $request->has('sortOrder')) {
            // Sorting
            $query->orderBy($request->get('sortField'), $request->get('sortOrder') == 1 ? 'asc' : 'desc');
//            $query->orderByRaw("LENGTH(".$request->get('sortField').") " . ($request->get('sortOrder') == 1 ? 'asc' : 'desc'));
        }

        // Pagination
        $page = $request->get('page', 1);
        $perPage = $request->get('all') ? $query->get()->count() : $request->get('perPage', 5);
        $filteredResultTotal = $query->get()->count();
        $filteredResult = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Buat temporary url file
        $filteredResult = $filteredResult->map(function ($item) {
            // Loop through the keys in the item's array
            foreach ($item->toArray() as $key => $value) {
                // Check if the key contains "path"
                if (strpos($key, 'path') !== false) {
                    // Create new keys with "url" and "url_download"
                    $link = $value ? \Storage::temporaryUrl($value, now()->addMinutes(30)) : null;
                    $download = $value ? \Storage::temporaryUrl(
                        $value,
                        now()->addMinutes(30),
                        ['ResponseContentDisposition' => 'attachment; filename="' . $value . '"']
                    ) : null;
                    $item[str_replace('path', 'url', $key)] = $link;
                    $item[str_replace('path', 'url_download', $key)] = $download;
                }
            }

            return $item;
        });

        return [
            'metadata' => [
                'filteredTotal' => $filteredResultTotal,
                'total' => $resultCount,
                'firstRow' => (!$filteredResultTotal || !$resultCount) ? 0 : ((($page - 1) * $perPage) + 1),
                'lastRow' => ((!$filteredResultTotal || !$resultCount) ? 0 : ((($page - 1) * $perPage) + $filteredResult->count()))
            ],
            'data' => $filteredResult
        ];

    }

    public static function queryMethodStatic($query, array $columns, Request $request)
    {
        // Jika dipanggil sebagai static method, buat instance dan panggil method
        return (new self)->queryMethod($query, $columns, $request);
    }

    private function matchmode(Builder $query, $column, $matchmode, $value, string $condition = 'and')
    {
        switch ($matchmode) {
            case 'startsWith':
                $query->where($column, 'like', $value . '%', $condition);
                break;

            case 'contains':
                $query->where($column, 'like', '%' . $value . '%', $condition);
                break;

            case 'notContains':
                $query->where($column, 'not like', '%' . $value . '%', $condition);
                break;

            case 'endsWith':
                $query->where($column, 'like', '%' . $value, $condition);
                break;

            case 'equals':
                $query->where($column, '=', $value, $condition);
                break;

            case 'notEquals':
                $query->where($column, '!=', $value, $condition);
                break;

            case 'lt':
            case 'lessThan':
                $query->where($column, '<', $value, $condition);
                break;

            case 'lte':
            case 'lessThanOrEqual':
                $query->where($column, '<=', $value, $condition);
                break;

            case 'gt':
            case 'greaterThan':
                $query->where($column, '>', $value, $condition);
                break;

            case 'gte':
            case 'greaterThanOrEqual':
                $query->where($column, '>=', $value, $condition);
                break;

            case 'dateIs':
                $date = date('Y-m-d', strtotime($value));
                $query->whereDate($column, '=', $date, $condition);
                break;

            case 'dateIsNot':
                $date = date('Y-m-d', strtotime($value));
                $query->whereDate($column, '!=', $date, $condition);
                break;

            case 'dateBefore':
                $date = date('Y-m-d', strtotime($value));
                $query->whereDate($column, '<', $date, $condition);
                break;

            case 'dateAfter':
                $date = date('Y-m-d', strtotime($value));
                $query->whereDate($column, '>', $date, $condition);
                break;
        }
    }
}
