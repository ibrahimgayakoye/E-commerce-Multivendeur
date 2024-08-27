<?php 

namespace App\Security\Voter;

use App\Entity\Products;
use PhpParser\Node\Stmt\Break_;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductsVoter extends Voter
{
    const EDIT = 'PRODUCT_EDIT';
    const DELETE ='PRODUCT_DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function supports(string $attribute, $product): bool
    {
        if(!in_array($attribute,[self::EDIT, self::DELETE])){
            return false;
        }
        if(!$product instanceof Products){
            return false;
        }
        return true;
    }

    public function voteOnAttribute($attribute, $product, TokenInterface $token): bool
    {
         // on recupere l'utilisateur a partir du token
         $user = $token->getUser();

         if(!$user instanceof UserInterface){
              return false;

         }

         // on verifie si l'utilisateur est admin

         if($this->security->isGranted('ROLE_ADMIN')) return true ;

         // on verifie les permissions

         switch($attribute){
            case self::EDIT:
                // on verifie si l'utilisateur peut 
                return $this->canEdit();
                Break;
            
            case self::DELETE:
                // on verifie si l'utilisateur peut supprimer
                return $this->canDelete();
                Break;
         }


    }

    private function canEdit(){
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDelete(){
        return $this->security->isGranted('ROLE_ADMIN');
    }


}