<?php

class Photo_Component {
	
	private $version = '1.0';
	
	private $datas = null;
	private $file_name = '';
	private $csv_data = null;
	
	public function __construct(&$datas) {
		$this->datas = $datas;
	}
	
	public function moveFile(){
		$attr = substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') );
		$randam_file_name = substr(md5(uniqid(mt_rand())),0,8).$attr;
		move_uploaded_file( $_FILES['file']['tmp_name'], SALON_UPLOAD_DIR.$randam_file_name);
		return $randam_file_name;
	}
	
	public function resizeFile($target_file_name,$target_width=100,$target_height=100){
		$target_file_path = SALON_UPLOAD_DIR.$target_file_name;
		$size = getimagesize($target_file_path);
	    if ($size[2] == IMAGETYPE_JPEG)	$im = imagecreatefromjpeg($target_file_path);
	    else if ($size[2] == IMAGETYPE_PNG )	$im = imagecreatefrompng($target_file_path);
	    else if ($size[2] == IMAGETYPE_GIF)			$im = imagecreatefromgif($target_file_path);

		$out = imagecreatetruecolor($target_width, $target_height);

		//求める画像サイズとの比を求める
		$width = $size[0];
		$height = $size[1];
		$width_gap = $width / $target_width;
		$height_gap = $height / $target_height;



		//横より縦の比率が大きい場合は、求める画像サイズより縦長
		// => 縦の上下をカット
		if ($width_gap < $height_gap) {
			$cut = ceil((($height_gap - $width_gap) * $target_height) / 2);
			imagecopyresampled($out, $im, 0, 0, 0, $cut, $target_width, $target_height, $width, $height - ($cut * 2));
		//縦より横の比率が大きい場合は、求める画像サイズより横長
		// => 横の左右をカット
		} else if ($height_gap < $width_gap) {
			$cut = ceil((($width_gap - $height_gap) * $target_width) / 2);
			imagecopyresampled($out, $im, 0, 0, $cut, 0, $target_width, $target_height, $width - ($cut * 2), $height);
		//縦横比が同じなら、そのまま縮小
		} else {
			imagecopyresampled($out, $im, 0, 0, 0, 0, $target_width, $target_height, $width, $height);
		}
		//ファイルの保存
		$resized_file_name = $target_width."_".$target_height."_".basename($target_file_path);
		imagepng( $out,  SALON_UPLOAD_DIR.$resized_file_name);

		//メモリ開放
		imagedestroy($im);
		imagedestroy($out);		
		
		return $resized_file_name;

	}

	public function editTableData($set_file_name,$set_resize_file_name) {
		$set_data['photo_name'] = stripslashes($_FILES['file']['name']);
		$set_data['photo_path'] = SALON_UPLOAD_URL.$set_file_name;
		$set_data['photo_resize_path'] = SALON_UPLOAD_URL.$set_resize_file_name;
		$set_data['width'] = 0;
		$set_data['height'] = 0;
		$set_data['delete_flg'] = Salon_Reservation_Status::TEMPORARY;

		return $set_data;		
	}
	
	public function deletePhotoData() {
		$set_data['photo_ids'] = str_replace("photo_id_","",stripslashes($_POST['photo_id']));
		$ids = explode(',',$set_data['photo_ids'] );
		//INSERTしたけど確定しなかった場合の対処
		foreach ($ids as $d1) {
			$res = $this->datas->getPhotoDataForDelete($d1);
			if (count($res) == 0 ) {
				throw new Exception(Salon_Component::getMsg('E901',__LINE__),__('NO PHOTO DATA',SL_DOMAIN));
			}
			$files = array($res[0]['photo_path'],$res[0]['photo_resize_path']);
			foreach ($files as $d2) {
				if ( ! unlink(SALON_UPLOAD_DIR.basename($d2)) ) {
					throw new Exception(Salon_Component::getMsg('E901',__LINE__),__('PHOTO DATA CAN\'T DELETE',SL_DOMAIN));
				}
			}
		}
//
		return $set_data;		
	}
	
	
	
}