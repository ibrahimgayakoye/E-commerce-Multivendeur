<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture

{
    private $counter = 1;
    public function __construct(private SluggerInterface $slugger)
    {
        
    }
    public function load(ObjectManager $manager): void
    {
        $parent = $this->createCategory(name:'Informatique',parent:null,manager: $manager);
        
        $this->createCategory('Ordinateurs Portable',$parent,manager: $manager);
        $this->createCategory('Ecrans',$parent,$manager);
        $this->createCategory('Souris',$parent,$manager);

        $parent = $this->createCategory('Mode', null, $manager);

        $this->createCategory('Homme', $parent,$manager);
        $this->createCategory('Femme',$parent,$manager);
        $this->createCategory('Enfant', $parent, $manager);


        


        $manager->flush();
    }

    public function createCategory(string $name, Categories $parent = null, ObjectManager $manager){
        $category = new Categories();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName()));
        $category->setParent($parent);
        $manager->persist($category);

        $this->addReference('cat-'.$this->counter, $category);
        $this->counter++;

        return $category;
    }
}
