<?php
class GetFiles {
    public function get_files($folder, $ext, $subfolders) {
        // Ajouter le / à la fin du nom du dossier
        if(substr($folder, -1) != '/')
            $folder .= '/';
        
        // Ouverture du répertoire
        $rep = @opendir($folder);
        if(!$rep)
            return [];
            
        $files = [];
        
        // Parcourir les fichiers
        while($file = readdir($rep))
        {
            // Ignorer . et ..
            if($file == '.' || $file == '..')
                continue;
            
            // Si c'est un sous-dossier et qu'on veut les parcourir
            if(is_dir($folder . $file) && $subfolders)
                $files = array_merge($files, $this->get_files($folder . $file, $ext, true));
            // Vérifier l'extension
            else if(is_array($ext)) {
                $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if(in_array('.' . $file_ext, array_map('strtolower', $ext)))
                    $files[] = $folder . $file;
            }
        }
        
        closedir($rep);
        return $files;
    }
    
    public function count_files($folder, $ext, $subfolders)
    {
        return count($this->get_files($folder, $ext, $subfolders));
    }
}