<?php
namespace App\Services;

class RetrieveChangesService {
    private $userId;
    private $dbService;
    private $userDatabaseService;
    private $playlistQueryService;

    public function __construct($userId) {
        $this->userId = $userId;
        $this->dbService = new DatabaseService();
        $this->userDatabaseService = new UserDatabaseService();
        $this->playlistQueryService = new PlaylistQueryService($this->userDatabaseService, $userId);
    }

    public function saveUpdatedPlaylist($taskId,$taskIntercepter = null, $destPlaylistIntercepter = null) {
        $taskIntercepter = $taskIntercepter ?? function() {};
        $destPlaylistIntercepter = $destPlaylistIntercepter ?? function($playlist) { return $playlist; };

        $playlist = $this->dbService->getTask($taskId, $this->userId);
        if(! $playlist) return null;
        $taskIntercepter($playlist);

        $destPlaylist = $this->playlistQueryService->query($playlist->getDestId(), $playlist->getDestType());
        $interceptedDestPlaylist = $destPlaylistIntercepter($destPlaylist);
        
        $sourcePlaylist = $this->playlistQueryService->query($playlist->getSourceId(), $playlist->getSourceType(), $playlist->isSourceAuthorized(), $interceptedDestPlaylist);
        $playlist->update($sourcePlaylist, $destPlaylist);
        
        $this->dbService->saveTask($playlist);
        return $playlist;
    }

}