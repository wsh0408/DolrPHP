<?php
class Tree
{

	/**
	 * 返回无限分类数组(一维数组)
	 *
	 * @param array   $cate  数据库查询的分类数组结果
	 * @param string  $html  层级填充
	 * @param integer $pid   父亲id
	 * @param integer $level 分类层级
	 *
	 * @return array
	 */
	public static function TreeForLevel($cate, $html = '--', $pid = 0, $level = 0)
	{
		$arr = array();
		if (is_array($cate)) {
			foreach ($cate as $v) {
				if ($v['parent_id'] == $pid) {
					$v['level'] = $level + 1;
					$v['html'] = str_repeat($html, $level)." ";
					$arr[] = $v;
					$arr = array_merge($arr, self::TreeForLevel($cate, $html,$v['id'],$level+1));
				}
			}
		}

		return $arr;
	}

	/**
	 * 返回无限分类数组(多维数组)
	 *
	 * @param array   $cate 数据库查询的分类数组结果
	 * @param string  $name 子类键名
	 * @param integer $pid  父ID
	 *
	 * @return array
	 */
	public static function TreeForLayer($cate, $name = 'child', $pid = 0)
	{
		$arr = array();
		if (is_array($cate)) {
			foreach ($cate as $v) {
				if ($v['parent_id'] == $pid) {
					$v[$name] = self::TreeForLayer($cate, $name, $v['id']);
					$arr[] = $v;
				}
			}
		}

		return $arr;
	}

	/**
	 * 返回指定ID的所有父类
	 *
	 * @param array   $cate 数据库查询的分类数组结果
	 * @param integer $id   当前类别的ID
	 *
	 * @return array
	 */
	public static function getParents($cate, $id)
	{
		$arr = array();
		if (is_array($cate)) {
			foreach ($cate as $v) {
				if ($v['id'] == $id) {
					$arr[] = $v;
					$arr = array_merge($arr, self::getParents($cate, $v['parent_id']));
				}
			}
		}

		return $arr;
	}

	/**
	 * 通过父类ID获取其下面所有的子类ID
	 *
	 * @param array   $cate 数据库查询的分类数组结果
	 * @param integer $pid  父ID
	 *
	 * @return array
	 */
	public static function getChlidsById($cate, $pid)
	{
		$arr = array();
		if (is_array($cate)) {
			foreach ($cate as $v) {
				if ($v['parent_id'] == $pid) {
					$arr[] = $v['id'];
					$arr = array_merge($arr, self::getChlidsById($cate, $v['id']));
				}
			}
		}

		return $arr;
	}

	/**
	 * 通过父类ID获取其下面所有的子类
	 *
	 * @param array   $cate 数据库查询的分类数组结果
	 * @param integer $pid  父ID
	 *
	 * @return array
	 */
	public static function getChlids($cate, $pid)
	{
		$arr = array();
		if (is_array($cate)) {
			foreach ($cate as $v) {
				if ($v['parent_id'] == $pid) {
					$arr[] = $v;
					$arr = array_merge($arr, self::getChlids($cate, $v['id']));
				}
			}
		}

		return $arr;
	}
}