<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Validator;
use App\Models\Airline;

class CreateAirlineCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yaams:create-airline
                            {name : The airline name}
                            {iataprefix : The IATA prefix (e.g. LH)}
                            {icaoprefix : The ICAO prefix (e.g. DLH)}
                            {atc-callsign : The ATC callsign (e.g. Lufthansa)}
                            {lbs : Set the weight unit to LBS instead of KG}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Creates a new airline to be used with YAAMS.";

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            "name" =>
                "What is the name of your airline? (E.g. Lufthansa Virtual)",
            "iataprefix" =>
                "What is the desired IATA prefix of the airline? This is used for flight numbers. (E.g. LH)",
            "icaoprefix" =>
                "What is the desired ICAO prefix of the airline? This is used for callsigns. (E.g. DLH)",
            "atc-callsign" =>
                "What is the ATC callsign of the airline? (E.g. Lufthansa)",
            "lbs" => function () {
                return $this->choice(
                    "Is your airline using LBS instead of KG for weight units?",
                    ["No", "Yes"],
                    0
                );
            },
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Get the user input
        $desiredName = $this->argument("name");
        $desiredIATA = $this->argument("iataprefix");
        $desiredICAO = $this->argument("icaoprefix");
        $desiredCallsign = $this->argument("atc-callsign");
        $desiredUnitLBS = $this->argument("lbs") === "Yes";

        //Validate it
        $validator = Validator::make(
            [
                "name" => $desiredName,
                "iataprefix" => $desiredIATA,
                "icaoprefix" => $desiredICAO,
                "atcCallsign" => $desiredCallsign,
                "isLBS" => $desiredUnitLBS,
            ],
            [
                //'name' => 'required|alpha',
                "name" => 'required|regex:/^[\pL\s]+$/u',
                "iataprefix" => "required|alpha_num|uppercase|size:2",
                "icaoprefix" => "required|alpha|uppercase|size:3",
                //"atcCallsign" => "required|alpha",
                "atcCallsign" => "required|regex:/^[\pL\s]+$/u",
                "isLBS" => "required|boolean",
            ]
        );

        //Check if validator fails
        if ($validator->fails()) {
            $this->info(
                "Airline could not be created. See error messages below:"
            );

            //Go through the errors
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            //Return exit code 1
            return 1;
        }

        // Retrieve validated data
        $validated = $validator->validated();

        $this->info("Your input: ");
        $this->newLine();
        $this->info("Airline Name: $desiredName");
        $this->info("IATA Prefix: $desiredIATA");
        $this->info("ICAO Prefix: $desiredICAO");
        $this->info("ATC Callsign: $desiredCallsign");
        $this->info("Uses LBS: " . ($desiredUnitLBS ? "Yes" : "No"));

        if (
            $this->confirm(
                "Is this correct? The airline will be created.",
                true
            )
        ) {
            $this->info("Okay, creating airline ...");

            // Create the airline with the validated data
            Airline::create([
                "name" => $validated["name"],
                "prefix" => $validated["iataprefix"],
                "icao_callsign" => $validated["icaoprefix"],
                "atc_callsign" => $validated["atcCallsign"],
                "unit_is_lbs" => $validated["isLBS"],
            ]);

            $this->info("Airline created successfully!");
        }
    }
}
