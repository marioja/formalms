<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */
require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
require_once($GLOBALS['where_lms'] . '/lib/lib.test.php');


class CoursereportLms extends Model
{

    const SOURCE_OF_TEST = 'test';
    const SOURCE_OF_SCOITEM = 'scoitem';
    const SOURCE_OF_ACTIVITY = 'activity';
    const SOURCE_OF_FINAL_VOTE = 'final_vote';


    const TEST_STATUS_NOT_COMPLETED = 'not_complete';
    const TEST_STATUS_NOT_CHECKED = 'not_checked';
    const TEST_STATUS_NOT_PASSED = 'not_passed';
    const TEST_STATUS_PASSED = 'passed';
    const TEST_STATUS_DOING = 'doing';
    const TEST_STATUS_VALID = 'valid';

    /**
     * @var int
     */
    protected $idCourse;
    /**
     * @var int
     */
    protected $idReport;
    /**
     * @var string
     */
    protected $sourceOf;

    /**
     * @var int
     */
    protected $idSource;

    /**
     * @var int
     */
    protected $reportCount;
    /**
     * @var ReportLms[]
     */
    protected $courseReports;


    /**
     * CoursereportLms constructor.
     * @param $idCourse
     * @param null $idReport
     * @param null $sourceOf
     * @param null $idSource
     */

    public function __construct($idCourse, $idReport = null, $sourceOf = null, $idSource = null)
    {
        $this->idCourse = $idCourse;

        $this->idReport = $idReport;

        $this->sourceOf = $sourceOf;

        $this->idSource = $idSource;

        $this->courseReports = array();
    }

    /**
     * @param int $idCourse
     */
    public function setIdCourse($idCourse)
    {
        $this->idCourse = $idCourse;

        $this->grabCourseReports();
    }

    /**
     * @return int
     */
    public function getIdCourse()
    {
        return $this->idCourse;
    }

    /**
     * @return ReportLms[]
     */
    public function getCourseReports()
    {
        if (count($this->courseReports) == 0) {
            $this->grabCourseReports();
        }
        return $this->courseReports;
    }


    /**
     * @return int
     */
    public function getReportCount()
    {
        $this->reportCount = count($this->courseReports);

        return $this->reportCount;
    }

    private function grabCourseReports()
    {
        $report_man = new CourseReportManager();
        $org_tests =& $report_man->getTest();

        $query_tot_report = "SELECT COUNT(*) "
            . " FROM " . $GLOBALS['prefix_lms'] . "_coursereport "
            . " WHERE id_course = '" . $_SESSION['idCourse'] . "'";
        list($tot_report) = sql_fetch_row(sql_query($query_tot_report));

        $query_tests = "SELECT id_report, id_source "
            . " FROM " . $GLOBALS['prefix_lms'] . "_coursereport "
            . " WHERE id_course = '" . $this->idCourse . "' AND source_of = 'test'";

        $re_tests = sql_query($query_tests);

        while (list($id_r, $id_t) = sql_fetch_row($re_tests)) {
            $included_test[$id_t] = $id_t;
            $included_test_report_id[$id_r] = $id_r;
        }

        // XXX: Update if needed
        if ($tot_report == 0 || $tot_report == 1) {
            $report_man->initializeCourseReport($org_tests);
        }
        /*else {

            if (is_array($included_test)) {
                $test_to_add = array_diff($org_tests, $included_test);
            } else {
                $test_to_add = $org_tests;
            }

            if (is_array($included_test)) {
                $test_to_del = array_diff($included_test, $org_tests);
            } else {
                $test_to_del = $org_tests;
            }

            if (!empty($test_to_add) || !empty($test_to_del)) {
                $report_man->addTestToReport($test_to_add, 1);
                $report_man->delTestToReport($test_to_del);

                $included_test = $org_tests;
            }
        }*/
        $report_man->updateTestReport($org_tests);


        $query_report = "SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
                        FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	                    WHERE id_course = '" . $this->idCourse . "'";

        if (!is_null($this->idReport)) {
            $query_report .= " AND id_report = '" . $this->idReport . "'";
        }

        if (!is_null($this->sourceOf)) {

            if (is_array($this->sourceOf)) {

                $query_report .= " AND source_of IN ('" . implode($this->sourceOf, "','") . "')";
            } else {
                $query_report .= " AND source_of = '" . $this->sourceOf . "'";
            }
        }

        if (!is_null($this->idSource)) {
            $query_report .= " AND id_source = '" . $this->idSource . "'";
        }

        $query_report .= " ORDER BY sequence ";

        $re_report = sql_query($query_report);

        while ($info_report = sql_fetch_assoc($re_report)) {

            $report = new ReportLms($info_report['id_report'], $info_report['title'], $info_report['max_score'], $info_report['required_score'], $info_report['weight'], $info_report['show_to_user'], $info_report['use_for_final'], $info_report['source_of'], $info_report['id_source']);

            $this->courseReports[] = $report;
        }


    }

    /**
     * Returns reports included for idcourse filrt
     *
     * @return  ReportLms[]
     */
    public function getReportsFilteredBySourceOf($sourceOf = null)
    {
        $result = array();

        foreach ($this->courseReports as $courseReport) {

            if ($sourceOf === null || $courseReport->getSourceOf() === $sourceOf) {
                $result[] = $courseReport;
            }
        }

        return $result;
    }


    /**
     * @param array $id_sources
     */
    public function getCourseReportsFilteredByIdSources($id_sources)
    {

        $result = array();

        if ($id_sources === null || count($id_sources) == 0) {
            return $result;
        }

        if (count($this->courseReports) == 0){
            $this->grabCourseReports();
        }

        foreach ($this->courseReports as $info_report) {

            switch ($info_report->getSourceOf()) {
                case CoursereportLms::SOURCE_OF_TEST : {

                    $testObj = Learning_Test::load($info_report->getIdSource());

                    if (in_array($testObj->getObjectType() . "_" . $info_report->getIdSource(), $id_sources)) {
                        $result[] = $info_report;
                        if (count($result) == count($id_sources)){
                            return $result;
                        }
                    }
                }
                    break;
                case CoursereportLms::SOURCE_OF_SCOITEM: {

                    $scormItem = new ScormLms($info_report->getIdSource());

                    if (in_array($info_report->getSourceOf() . "_" . $scormItem->getIdSource(), $id_sources)) {

                        $result[] = $info_report;
                        if (count($result) == count($id_sources)){
                            return $result;
                        }
                    }
                }
                    break;
                case CoursereportLms::SOURCE_OF_ACTIVITY: {

                    if (in_array($info_report->getSourceOf() . "_" . $info_report->getIdSource(), $id_sources)) {
                        $result[] = $info_report;
                        if (count($result) == count($id_sources)){
                            return $result;
                        }
                    }
                }
                    break;
                default: {

                }
            }
        }

        return $result;
    }

    /**
     * @return ReportLms[]
     */
    public function getReportsForFinal()
    {

        $reports = array();

        foreach ($this->courseReports as $courseReport) {

            if ($courseReport->isUseForFinal() && $courseReport->getSourceOf() != 'final_vote') {
                $reports[] = $courseReport;
            }
        }

        return $reports;
    }

    /**
     * @param $idCourse
     * @return ReportLms
     */
    public static function getReportFinalScore($idCourse)
    {

        $query_report = "SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
		FROM " . $GLOBALS['prefix_lms'] . "_coursereport
		WHERE id_course = '" . $idCourse . "' AND source_of = 'final_vote' AND id_source = '0'";

        $re_report = sql_query($query_report);

        while ($info_report = sql_fetch_assoc($re_report)) {

            $report = new ReportLms($info_report['id_report'], $info_report['title'], $info_report['max_score'], $info_report['required_score'], $info_report['weight'], $info_report['show_to_user'], $info_report['use_for_final'], $info_report['source_of'], $info_report['id_source']);

        }

        return $report;
    }

    public function getReportsId($sourceOf = null)
    {
        $responseArray = array();

        foreach ($this->courseReports as $courseReport) {

            if ($sourceOf === null || $courseReport->getSourceOf() === $sourceOf) {

                $responseArray[] = $courseReport->getIdReport();
            }
        }

        return $responseArray;
    }

    public function getSourcesId($sourceOf = null)
    {
        $responseArray = array();

        foreach ($this->courseReports as $courseReport) {

            if ($sourceOf === null || $courseReport->getSourceOf() === $sourceOf) {

                $responseArray[] = $courseReport->getIdSource();
            }
        }

        return $responseArray;
    }
}