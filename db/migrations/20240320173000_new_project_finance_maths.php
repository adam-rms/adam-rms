<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;


final class NewProjectFinanceMaths extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        $this->execute("alter table projects add projects_dates_finances_days SMALLINT UNSIGNED default NULL null after projects_dates_deliver_end;
            alter table projects add projects_dates_finances_weeks SMALLINT UNSIGNED default NULL null after projects_dates_finances_days;");

        $builder = $this->getQueryBuilder();
        $projectsQuery = $builder->select(['projects_id', 'projects_dates_deliver_start', 'projects_dates_deliver_end', 'projects_name'])->from('projects')->execute();
        $projects = $projectsQuery->fetchAll();
        
        foreach ($projects as $project) {
            if ($project[1] == null || $project[2] == null) {
                continue;
            }
            echo "Starting migration of project " . $project[3] . " dates \n";
            $return = ["string" => "Project finance migrated in upgrade of AdamRMS. Project number of days & weeks set manually, calculated based on:", "days" => 0, "weeks" => 0];
            $start = strtotime(date("d F Y 00:00:00", strtotime($project[1])));
            $end = strtotime(date("d F Y 23:59:59", strtotime($project[2])));
            if (date("N", $start) == 6) {
                $return['weeks'] += 1;
                $return['string'] .= "\nBegins on Saturday so first weekend charged as one week";
                $start = $start + (86400 * 2);
            } elseif (date("N", $start) == 7) {
                $return['weeks'] += 1;
                $return['string'] .= "\nBegins on Sunday so first weekend charged as one week";
                $start = $start + 86400;
            }
            if (($end-$start) > 259200) { //If it's just one weekend it doesn't count as two weeks
                if (date("N", $end) == 6) {
                    $return['weeks'] += 1;
                    $return['string'] .= "\nEnds on Saturday so last weekend charged as one week";
                    $end = $end - 86400;
                } elseif (date("N", $end) == 7) {
                    $return['weeks'] += 1;
                    $return['string'] .= "\nEnds on Sunday so last weekend charged as one week";
                    $end = $end - (86400 * 2);
                }
            }
            $remaining = strtotime(date("d F Y 23:59:59", $end)) - strtotime(date("d F Y", $start));
            if ($remaining > 0) {
                $remaining = ceil($remaining / 86400); //Convert to days
                $weeks = floor($remaining / 7); //Number of week periods
                if ($weeks > 0) {
                    $return['weeks'] += $weeks;
                    if ($weeks == 1) {
                        $return['string'] .= "\nAdd 1 week period to reflect a period of more than 7 days";
                    } else {
                        $return['string'] .= "\nAdd " . $weeks . " week periods to reflect a period of more than 7 days";
                    }
                    $remaining = $remaining - ($weeks * 7);
                }
                if ($remaining > 2) {
                    $return['string'] .= "\nAdd a week to discount a period between 3 and 7 days";
                    $return['weeks'] += 1;
                    $remaining = $remaining - 7;
                }
                if ($remaining > 0) {
                    $return['days'] += ceil($remaining);
                    if ($remaining == 1){
                        $return['string'] .= "\nAdd 1 day period";
                    } else {
                        $return['string'] .= "\nAdd " . ceil($remaining) . " day periods";
                    }
                }
            }
            $projectBuilder = $this->getQueryBuilder();
            $projectBuilder
                ->update('projects')
                ->set(['projects_dates_finances_days' => $return['days'], 'projects_dates_finances_weeks' => $return['weeks']])
                ->where(['projects_id' => $project[0]])
                ->execute();
            $this->table('auditLog')->insert([
                'auditLog_actionType' => 'CHANGE-DATE-FINANCE',
                "auditLog_timestamp" =>  date("Y-m-d H:i:s"),
                'auditLog_actionTable' => 'projects',
                'auditLog_actionData' => $return['string'],
                'projects_id' => $project[0],
            ])->saveData();
        }
    }
}
