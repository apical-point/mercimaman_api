<?php namespace App\Libraries;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomPaginator extends LengthAwarePaginator
{
    public function toArray() : array
    {
        // ids配列
        $ids = [];
        if($this->total()!=0) {
            foreach($this->items->toArray() as $row) {
                if(!empty($row['id'])) $ids[] = $row['id'];
            }
        }

        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'per_page' => $this->perPage(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
            'ids' => $ids,
        ];
    }


}
