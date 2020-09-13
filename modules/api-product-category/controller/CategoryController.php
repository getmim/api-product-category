<?php
/**
 * CategoryController
 * @package api-product-category
 * @version 0.0.1
 */

namespace ApiProductCategory\Controller;

use Product\Model\Product;
use ProductCategory\Model\ProductCategory as PCategory;
use ProductCategory\Model\ProductCategoryChain as PCChain;
use LibFormatter\Library\Formatter;

class CategoryController extends \Api\Controller
{
	public function indexAction() {
        if(!$this->app->isAuthorized())
            return $this->resp(401);

        $cond = [];
        if($q = $this->req->getQuery('q'))
            $cond['q'] = $q;
        if($parent = $this->req->getQuery('parent'))
            $cond['parent'] = $parent;

        $cats = PCategory::get($cond, 0, 1, ['name'=>true]);
        if($cats)
        	$cats = Formatter::formatMany('product-category', $cats);

        foreach($cats as &$cat)
            $cat->meta = null;
        unset($cat);

        $this->resp(0, $cats, null, [
            'meta' => [
                'total' => PCategory::count($cond)
            ]
        ]);
    }

    public function productAction(){
    	if(!$this->app->isAuthorized())
            return $this->resp(401);

        $identity = $this->req->param->identity;
        $cat = PCategory::getOne(['id'=>$identity]);
        if(!$cat)
        	$cat = PCategory::getOne(['slug'=>$identity]);
        if(!$cat)
        	return $this->show404();

        list($page, $rpp) = $this->req->getPager(12, 24);

        $cond = [
            'product.status'   => 2,
            'product_category' => $cat->id
        ];
        if($q = $this->req->getQuery('q'))
            $cond['product.name'] = ['__like', $q];

        $price_min = $this->req->getQuery('price_min');
        $price_max = $this->req->getQuery('price_max');
        if($price_min || $price_max){
            $price_cond = [];
            if($price_min && $price_max)
                $price_cond = ['__between', $price_min, $price_max];
            elseif($price_min)
                $price_cond = ['__op', '>=', $price_min];
            else
                $price_cond = ['__op', '<=', $price_max];

            $cond['product.price_min'] = $price_cond;
        }

        // sortable
        $sort_options = [
            'created'  => 'created',
            'price'    => 'price_min'
        ];
        if(module_exists('product-stat'))
        	$sort_options['stat'] = 'stat';

        $sort_by = $this->req->getQuery('sort', 'created');
        if(!isset($sort_options[$sort_by]))
            $sort_by = 'created';

        $sort = $sort_options[$sort_by];
        $by   = $this->req->getQuery('by', 'DESC');
        if(!in_array($by, ['ASC', 'DESC']))
        	$by = 'DESC';

        $products = [];

        $pchains = PCChain::get($cond, $rpp, $page, ['product.' . $sort=>($by==='ASC')]);
        if($pchains){
        	$product_ids = array_column($pchains, 'product');
        	$products    = Product::get(['id'=>$product_ids], 0, 1, [$sort=>($by==='ASC')]);
            $fmt = [
                'user' => ['address'=>['state']],
                'category'
            ];
        	$products    = Formatter::formatMany('product', $products, $fmt);

            foreach($products as &$product){
                $product->gallery = [];
                $product->meta = null;
                $product->content = null;
                foreach($product->category as &$cat)
                    $cat->meta = null;
                unset($cat);
            }
            unset($product);
        }

        $this->resp(0, $products, null, [
            'meta' => [
                'page'  => $page,
                'rpp'   => $rpp,
                'total' => PCChain::count($cond)
            ]
        ]);
    }

    public function singleAction(){
    	if(!$this->app->isAuthorized())
            return $this->resp(401);

        $identity = $this->req->param->identity;
        $cat = PCategory::getOne(['id'=>$identity]);
        if(!$cat)
        	$cat = PCategory::getOne(['slug'=>$identity]);
        if(!$cat)
        	return $this->show404();

        $cat = Formatter::format('product-category', $cat, ['parent']);

        $this->resp(0, $cat);
    }
}