<?php namespace App\Repositories;

use App\Repositories\BaseRepositoryInterface\BaseBasicInterface;

interface MessageRepositoryInterface extends BaseBasicInterface
{

    public function createImageByMessageId($messageId, $imageData);
}
