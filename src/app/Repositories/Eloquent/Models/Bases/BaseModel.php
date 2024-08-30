<?php namespace App\Repositories\Eloquent\Models\Bases;

use Illuminate\Database\Eloquent\Model;
use Validator;

// カスタムエラー
use App\Exceptions\CustomException;

class BaseModel extends Model
{
	private $errors;

	public function validate($data)
	{
		// バリデート行う
		$v = Validator::make($data, $this->rules);
		if(!$v->fails()) return true;

		// 失敗ならば、そのエラーを返す。
		$this->errors = $v->errors();
		return false;
	}

	//   エラーのオブジェクトを取得できる
  	public function errors()
  	{
    	return $this->errors;
	}

	// なぜかupdate()はオーバーライドできなかった。エラーとなるみたいだ
	// public function customUpdate($data)
	// {
	// 	// if (!$this->validate($data)) throw new CustomException($this->errors()->toArray(), 499);
	// 	return parent::update($data);
	// }


	// オーバーライド
	public function create($data)
	{
		if (!$this->validate($data)) throw new CustomException($this->errors()->toArray(), 499);
		return parent::create($data);
	}

	// // オーバーライド
	public function updateOrCreate($where, $data)
	{
		if (!$this->validate($data)) throw new CustomException($this->errors()->toArray(), 499);
		return parent::updateOrCreate($where, $data);
	}

	// オーバーライド
	public function fill($data)
	{
		if (!$this->validate($data)) throw new CustomException($this->errors()->toArray(), 499);
		return parent::fill($data);
	}

	// // オーバーライド
	// public function paginate($num)
	// {
	// 	$paginate = parent::paginate($num);
	// 	dd($paginate);
	// }

	// 正しいカラムのみを取得する
	public function getColumns(array $columns=[])
	{
		// 結果配列定義
		$results = [];

		// 入力がなければ終了
		if(empty($columns)) return [];

		// fillableがなければ終了
		if(empty($this->fillable)) return [];

		// 該当するカラムがあれば取得
		foreach ($columns as $columnName) {

			// 「$fillableにある and $hiddenにない」 or primaryKey
			if(in_array($columnName, $this->fillable) && !in_array($columnName, $this->hidden) || $columnName==$this->primaryKey) $results[] = $columnName;
		}

		return $results;
	}



	// --------------------------- 便利な関数の作成 ---------------------------
	// idで1件の取得
	// public function getItemById($id)
	// {
	// 	return parent::where(['id'=>$id])->first();
	// }



}
