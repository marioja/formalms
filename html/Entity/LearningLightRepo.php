<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningLightRepo
 *
 * @ORM\Table(name="learning_light_repo")
 * @ORM\Entity
 */
class LearningLightRepo
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id_repository", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idRepository;

    /**
     * @var int
     *
     * @ORM\Column(name="id_course", type="integer", nullable=false)
     */
    private $idCourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="repo_title", type="string", length=255, nullable=false)
     */
    private $repoTitle = '';

    /**
     * @var string
     *
     * @ORM\Column(name="repo_descr", type="string", length=65536, nullable=false)
     */
    private $repoDescr;

        /**
     * @var string
     *
     * @ORM\Column(name="repo_teacher_alert", type="boolean", nullable=true, options={"default"=0})
     */
    private $repoTeacherAlert;

}
