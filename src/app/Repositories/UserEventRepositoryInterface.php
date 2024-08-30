<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface UserEventRepositoryInterface extends BaseBasicInterface
{

    public function updateOrCreateImageData($Id, $where, $imageData);


    public function createEventMemberItem($data);

    public function getEventMemberItem($where);

    public function getEventMemberItems($where, $take, $orderByRaw);

    public function updateEventMemberItem($where, $data);

    public function getEventMemberList($search);

}
