<?php


namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTracksession
 *
 * @ORM\Table(name="learning_tracksession",
 *  indexes={
 *     @ORM\Index(name="idUser_active_idx", columns={"idUser","active"})
 *  }
 * )
 * @ORM\Entity
 */
class LearningTracksession
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="idEnter", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $identer;

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     */
    private $iduser = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="string", length=255, nullable=false)
     */
    private $sessionId = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="enterTime", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $entertime = null;

    /**
     * @var int
     *
     * @ORM\Column(name="numOp", type="integer", nullable=false)
     */
    private $numop = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lastFunction", type="string", length=50, nullable=false)
     */
    private $lastfunction = '';

    /**
     * @var string
     *
     * @ORM\Column(name="lastOp", type="string", length=5, nullable=false)
     */
    private $lastop = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastTime", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $lasttime = null;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=40, nullable=false)
     */
    private $ipAddress = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = '0';


}
