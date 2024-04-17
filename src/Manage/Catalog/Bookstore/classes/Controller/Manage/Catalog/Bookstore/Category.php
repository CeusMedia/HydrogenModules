<?php
class Controller_Manage_Catalog_Bookstore_Category extends Controller_Manage_Catalog_Bookstore
{
	protected Logic_Catalog_BookstoreManager $logic;

	/**
	 *	@param		string		$categoryId
	 *	@param		string		$articleId
	 *	@param		string		$direction
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function rankArticle( string $categoryId, string $articleId, string $direction ): void
	{
		$model		= new Model_Catalog_Bookstore_Article_Category( $this->env );
		$category	= $this->logic->getCategory( $categoryId );
		$article	= $this->logic->getArticle( $articleId );
		$articles	= $this->logic->getCategoryArticles( $category, ['rank' => 'ASC'] );
		foreach( $articles as $nr => $item ){
			if( $item->articleId == $article->articleId ){
				if( $direction === "up" ){
					if( $nr > 0 ){
						$other	= $articles[$nr - 1];
						$model->edit( $other->articleCategoryId, ['rank' => $item->rank] );
						$model->edit( $item->articleCategoryId, ['rank' => $other->rank] );
					}
					break;
				}
				else if( $direction === "down" ){
					if( ( $nr + 1 ) < count( $articles ) ){
						$other	= $articles[$nr + 1];
						$model->edit( $other->articleCategoryId, ['rank' => $item->rank] );
						$model->edit( $item->articleCategoryId, ['rank' => $other->rank] );
					}
					break;
				}
			}
		}
		$this->restart( './manage/catalog/bookstore/category/edit/'.$categoryId );
	}

	public function add( ?string $parentId = NULL ): void
	{
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'add' );
			$data	= $this->request->getAll();
			if( !strlen( $data['label_de'] ) )
				$this->messenger->noteError( $words->msgErrorLabelMissing );
			else{
				$categoryId	= $this->logic->addCategory( $data );
				$this->restart( 'manage/catalog/bookstore/category/edit/'.$categoryId );
			}
		}
		$model		= new Model_Catalog_Bookstore_Category( $this->env );
		$category	= [];
		foreach( $model->getColumns() as $column )
			$category[$column]	= $this->request->get( $column );
		$category['parentId']	= (int) $parentId;
		$this->addData( 'category', (object) $category );
		$this->addData( 'categories', $this->logic->getCategories( [], ['rank' => 'ASC'] ) );
	}

	public function edit( string $categoryId ): void
	{
		$words		= (object) $this->getWords( 'edit' );
		$category	= $this->logic->getCategory( $categoryId );
		if( !$category ){
			$this->messenger->noteError( $words->msgErrorInvalidId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			if( !strlen( $data['label_de'] ) )
				$this->messenger->noteError( $words->msgErrorLabelMissing );
			else{
				$this->logic->editCategory( $categoryId, $data );
				$this->restart( 'manage/catalog/bookstore/category/edit/'.$categoryId );
			}
		}
		$this->addData( 'category', $this->logic->getCategory( $categoryId ) );
		$this->addData( 'categories', $this->logic->getCategories( [], ['rank' => 'ASC'] ) );
		$this->addData( 'nrArticles', $this->logic->countArticlesInCategory( $categoryId, TRUE ) );
		$this->addData( 'articles', $this->logic->getCategoryArticles( $category, ['rank' => 'ASC'] ) );
	}

	public function index(): void
	{
		$this->addData( 'categories', $this->logic->getCategories() );
	}

	public function remove( string $categoryId ): void
	{
		$words		= (object) $this->getWords( 'remove' );
		$category	= $this->logic->getCategory( $categoryId );
		if( !$category ){
			$this->messenger->noteError( $words->msgErrorInvalidId );
			$this->restart( NULL, TRUE );
		}
		if( $this->logic->countArticlesInCategory( $categoryId, TRUE ) ){
			$this->messenger->noteError( $words->msgErrorNotEmpty );
			$this->restart( 'edit/'.$categoryId, TRUE );
		}
		$this->logic->removeCategory( $categoryId );
		$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $category->label_de, ENT_QUOTES, 'UTF-8' ) );
		$this->restart( ( $category->parentId ? 'edit/'.$category->parentId : NULL ), TRUE );
	}
}
