<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAdviceuser
 *
 * @ORM\Table(name="learning_adviceuser", indexes={
 *     @ORM\Index(name="idAdvice_idx", columns={"idAdvice"}),
 *     @ORM\Index(name="idUser_idx", columns={"idUser"})
 * })
 * @ORM\Entity
 */
class LearningAdviceuser
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
     * @ORM\Column(name="idAdvice", type="integer", nullable=false)
     
     */
    private $idadvice = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     
     */
    private $iduser = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="archivied", type="boolean", nullable=false)
     */
    private $archivied = '0';


}
