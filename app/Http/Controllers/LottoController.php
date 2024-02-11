<?php

namespace App\Http\Controllers;

use App\Exports\LottoDataExport;
use App\Imports\LottoDataImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class LottoController extends Controller
{
    private $ticketsFolder = 'SFTP/Tickets';
    private $winningsFolder = 'SFTP/Winnings';

    public function process()
    {
        $processDraw = $this->fileDirectory();

        if ($processDraw) {
            $results = [];
            // get all the files
            $ticketFiles = Storage::allFiles($this->ticketsFolder);
            $winningFiles = Storage::allFiles($this->winningsFolder);

            // get all the data for draw
            $ticketsPerCountry = $this->getDataPerCountry($ticketFiles);
            $winningsPerCountry = $this->getDataPerCountry($winningFiles);

            // compare the tickets to winnings | country level
            foreach ($ticketsPerCountry as $country => $countryData) {
                // compare the tickets to winnings | country date level
                foreach ($countryData as $date => $draws) {
                    // compare the tickets to winnings | country date draw level
                    foreach ($draws as $draw => $data) {
                        $title = $country . '_' . $date . '_' . $draw;
                        $results[$title] = $this->compareWinnings($data, $winningsPerCountry[$country][$date][$draw][0]);
                    }
                }
            }

            // export to file
            foreach ($results as $title => $result) {
                $export = new LottoDataExport($result);
                Excel::store($export, $title . '.csv');
            }
        }
    }

    /**
     * Compare draws to winnings
     *
     * @param array $tickets
     * @param array $winning
     * @return array
     */
    public function compareWinnings($tickets, $winning)
    {
        $results = [];

        if (!empty($tickets) && !empty($winning)) {
            foreach ($tickets as $ticket) {
                $ticketId = trim($ticket['ticket_id']);
                $results[$ticketId]['ticket_id'] = $ticketId;
                // break the ball numbers into an array
                $player = explode(':', trim($ticket['mainballs']));
                $lottery = explode(':', trim($winning['mainballs']));

                // compare main ball numbers
                $comparison = array_intersect($player, $lottery);

                // count the number of balls matched
                $results[$ticketId]['mainballs'] = count($comparison);

                // compare sub1 balls
                $results[$ticketId]['sub1'] = (!empty($winning['sub1']) && $winning['sub1'] == $ticket['sub1']);

                // compare sub2 balls
                $results[$ticketId]['sub2'] = (!empty($winning['sub2']) && $winning['sub2'] == $ticket['sub2']);

                // jackpot or not
                $results[$ticketId]['comment'] = (count($comparison) == count($lottery)) ? 'Jackpot Won' : '';
            }
        }

        return $results;
    }

    /**
     * Read the tickets and winnings files
     *
     * @param array $dataFiles
     * @return array
     */
    public function getDataPerCountry($dataFiles)
    {
        $datasPerCountry = [];

        foreach ($dataFiles as $file) {
            // get the data data
            $dataInfo = $this->getFileInfo($file);
            $dataArray = Excel::toArray(new LottoDataImport, Storage::path($file));

            if (!empty($dataArray)) {
                // pass first sheet $dataArray[0]
                $datasPerCountry[$dataInfo['country']][$dataInfo['date']][$dataInfo['draw']] = $this->cleanFileData($dataArray[0]);
            }
        }

        return $datasPerCountry;
    }

    /**
     * Get the country of ticket
     *
     * @param string $file
     * @return string
     */
    public function getFileInfo($file)
    {
        // get ticket info from path
        $path = explode('/', $file);
        // get ticket info from file name
        $fileInfo = explode('_', $path[2]);

        return [
            'country' => $fileInfo[0],
            'date' => $fileInfo[1],
            'draw' => trim(str_replace('result', '', substr($fileInfo[2], 0, strpos($fileInfo[2], '.'))))
        ];
    }

    /**
     * Clean provided CSV data
     *
     * @param array $dirtyData
     * @return array
     */
    public function cleanFileData($dirtyData)
    {
        $cleanArray = [];

        // check that ticket data is not empty
        if ($dirtyData) {
            // loop throught he data
            foreach ($dirtyData as $data) {
                // only add valid data to return array
                if (!empty($data['mainballs'])) {
                    $cleanArray[] = $data;
                }
            }
        }

        return $cleanArray;
    }

    /**
     * Validate data exists
     *
     * @return bool
     */
    public function fileDirectory()
    {
        $status = true;

        // check if folder exist
        if (!Storage::exists('SFTP')) {
            // create the folders
            Storage::makeDirectory($this->ticketsFolder);
            Storage::makeDirectory($this->winningsFolder);
            // exit status since no file exists
            $status = false;
        }

        // check files exist
        $ticketFiles = Storage::allFiles($this->ticketsFolder);
        $winningFiles = Storage::allFiles($this->winningsFolder);

        if (empty($ticketFiles) || empty($winningFiles)) {
            // exit processing no files found
            $status = false;
        }

        return $status;
    }
}
