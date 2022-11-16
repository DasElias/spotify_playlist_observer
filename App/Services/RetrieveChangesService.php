<?php
namespace App\Services;
use App\Models\WatchedPlaylist;

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

    public function saveUpdatedPlaylist($taskId, $taskIntercepter = null, $destPlaylistIntercepter = null) {
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

    public function insertNewPlaylist($sourceId, $sourceType, $destId, $destType, $isSourceAuthorized) {
        $destPlaylist = $this->playlistQueryService->query($destId, $destType);
        $sourcePlaylist = $this->playlistQueryService->query($sourceId, $sourceType, $isSourceAuthorized, $destPlaylist);
    
        if($this->dbService->doesTaskExist($sourceId, $destId)) {
            throw new OwnerDoesntMatchException("id of the owner doesnt match", $destPlaylist["collaborative"]);
        }

        $playlist = WatchedPlaylist::withApiResponse($sourcePlaylist, $destPlaylist, $sourceType, $destType, $isSourceAuthorized);
        if($this->dbService->doesTaskExist($sourceId, $destId)) {
            throw new TaskAlreadyExistsException("task already exists in database");
        }

        $playlist->update($sourcePlaylist, $destPlaylist);
        $this->dbService->saveTask($playlist);
        return $playlist;

    }

}