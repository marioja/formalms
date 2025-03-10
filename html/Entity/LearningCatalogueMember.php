<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCatalogueMember
 *
 * @ORM\Table(name="learning_catalogue_member", indexes={
 *     @ORM\Index(name="idst_member_idx", columns={"idst_member"}),
 *     @ORM\Index(name="id_catalogue_idx", columns={"idCatalogue"})
 * })
 * @ORM\Entity
 */
class LearningCatalogueMember
{

    use Timestamps;

    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idst_member", type="integer", nullable=false)
     
     */
    private $idstMember = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCatalogue", type="integer", nullable=false)
     
     */
    private $idcatalogue = '0';


}
