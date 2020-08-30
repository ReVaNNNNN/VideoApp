<?php


namespace App\Utils;


use App\Twig\AppExtension;
use App\Utils\AbstractClasses\CategoryTreeAbstract;

class CategoryTreeFrontPage extends CategoryTreeAbstract
{
    /**
     * @var AppExtension
     */
    private $slugger;
    private $mainParentName;
    private $mainParentId;
    private $currentCategoryName;

    public function getCategoryListAndParent(int $id): string
    {
        $this->slugger = new AppExtension();
        $parentData = $this->getMainParent($id);

        $this->mainParentName = $parentData['name'];
        $this->mainParentId = $parentData['id'];

        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));
        $this->currentCategoryName = $this->categoriesArrayFromDb[$key]['name'];

        $categoriesArray = $this->buildTree($parentData['id']);

        return $this->getCategoryList($categoriesArray);
    }
    public function getCategoryList(array $categoriesArray)
    {
        $this->categoryList .= '<ul>';

        foreach ($categoriesArray as $value) {
            $catName = $this->slugger->slugify($value['name']);
            $url = $this->urlGenerator->generate('video_list', ['category_name' => $catName, 'id' => $value['id']]);
            $this->categoryList .= '<li><a href="' . $url . '">' . $catName . '</a>';

            if (!empty($value['children'])) {
                $this->getCategoryList($value['children']);
            }
        }

        $this->categoryList .= '</ul>';

        return $this->categoryList;
    }

    public function getMainParent(int $id): array
    {
        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));

        if ($this->categoriesArrayFromDb[$key]['parent_id'] !== null) {
            return $this->getMainParent($this->categoriesArrayFromDb[$key]['parent_id']);
        } else {
            return [
                'id' => $this->categoriesArrayFromDb[$key]['id'],
                'name' =>  $this->categoriesArrayFromDb[$key]['name']
                ];
        }
    }
}