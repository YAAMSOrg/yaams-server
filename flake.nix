{
  description = "YAAMS dev shell with PHP";

  inputs = {
    nixpkgs.url = "github:nixos/nixpkgs?ref=nixos-unstable";
  };

  outputs = { self, nixpkgs }:
  let
  	system = "x86_64-linux";
	pkgs = nixpkgs.legacyPackages.${system};
  in
  {
	devShells.${system}.default =
	  pkgs.mkShell
	    {
		buildInputs = [
			pkgs.php85
			pkgs.php85Packages.composer
		];
		
		shellHook = ''
		  echo YAAMS dev shell
		'';
  	    };
   };
}
