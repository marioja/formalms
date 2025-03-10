<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTeacherProfile
 *
 * @ORM\Table(name="learning_teacher_profile", indexes={
 *      @ORM\Index(name="id_user_idx", columns={"id_user"})
 * })
 * @ORM\Entity
 */
class LearningTeacherProfile
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
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     
     */
    private $idUser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="curriculum", type="string", length=65536, nullable=false)
     */
    private $curriculum;

    /**
     * @var string
     *
     * @ORM\Column(name="publications", type="string", length=65536, nullable=false)
     */
    private $publications;


}
