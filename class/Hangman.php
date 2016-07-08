<?php
class Hangman
{
  #Word to guess as a character array, associated
  #with a boolean to determine if a particular char
  #should be revealed.
  private $word;
  #Bank of letters to guess as character array.
  private $bank;
  #The current hangman count. 6 = game over!
  private $hangCount;

  /*
  Constuctor loads/saves game state and reads action tags
  to determine what to do.
  */
  public function __construct()
  {
    if(!isset($_GET['action']))
    {
      $_GET['action'] = 'new_game';
    }

    if($this->isNewGame())
    {
      $this->resetGame();
      $this->fetchWord();
      $this->hangCount = 0;
      $bankLetters = 'abcdefghijklmnopqrstuvwxyz';
      $this->bank = str_split($bankLetters);
    }
    else
    {
      $this->loadSession();
      if(!$this->isGameOver() &&
        $this->isGuessL())
      {
          $letter = $_GET['letter'];
          $this->checkLetter($letter . $_SESSION['letter']);
      }
    }
    $this->saveSession();

    //DEBUG. Uncommet below for useful game state info.
    //var_dump($_GET['action']);
    //var_dump($this->word);
    //var_dump($this->hangCount);
    //var_dump($this->bank);
  }

  /*
  Fetches a random word from hangwords.txt. Stores the
  word as a character array in $_Session["word"]
  */
  public function fetchWord()
  {
    $myfile = file_get_contents(PHPWS_SOURCE_DIR . 'mod/hangman/hangwords.txt');
    $words = preg_split('/[\s]+/',$myfile);
    $randWord = strtolower($words[rand(0,count($words))]);
    $letters = str_split($randWord);
    for($x = 0; $x < count($letters); $x++)
    {
      if(isset($this->word[$letters[$x]]))
      {
        $_SESSION[$letters[$x]]++;
        $this->word[$letters[$x] . $_SESSION[$letters[$x]]] = false;
      }
      else
      {
        $_SESSION[$letters[$x]] = 0;
        $this->word[$letters[$x]] = false;
      }
    }
  }

  /*
  Sets session variables to global variables.
  */
  public function saveSession()
  {
    $_SESSION['word'] = $this->word;
    $_SESSION['hangCount'] = $this->hangCount;
    $_SESSION['bank'] = $this->bank;
  }

  /*
  Sets global variables to session variables.
  */
  public function loadSession()
  {
    $this->word=$_SESSION['word'];
    $this->bank=$_SESSION['bank'];
    $this->hangCount=$_SESSION['hangCount'];
  }

  /*
  Re-initializes the game. All super global variables
  cleared.
  */
  public function resetGame()
  {
    unset($_SESSION['word']);
    unset($_SESSION['hangCount']);
    unset($_SESSION['bank']);
  }

  /*
  Increases the hang count by one.
  */
  public function incrementHangCount()
  {
    $this->hangCount++;
  }

  /*
  Removes a letter from the letterbank. Finds its index
  in the letterbank, then unsets it.
  */
  public function bankRemoveLetter($letter)
  {
    $index = array_search($letter, $this->bank);
    unset($this->bank[$index]);
    return !($index === FALSE);
  }

  /*
  Checks to see if a letter is in the word to guess. If the
  letter is in the word, it is revealed and removed from letter
  bank. Otherwise, hang count is incremented. Boolean is used
  to make sure it hasn't already been removed, but this
  shouldn't be possible in the view.
  */
  public function checkLetter($letter)
  {
    if (array_key_exists($letter, $this->word))
    {
      $this->revealLetter($letter);
      $this->bankRemoveLetter($letter);
    }
    else
    {
      if($this->bankRemoveLetter($letter))
      {
        $this->incrementHangCount();
      }
    }
  }

  /*
  Updates $word to indicate that the input letter
  has been revealed.
  */
  public function revealLetter($letter)
  {
    for($x = $_SESSION[$letter]; $x > 0; $x--)
    {
      $index = array_search($letter . $x, $this->bank);
      $this->word[$letter . $x] = true;
    }
    $this->word[$letter] = true;
  }

  /*
  Sets all of $word to true, revealing the whole word. Used
  when the player users to show the answer.
  */
  public function revealWord()
  {
    foreach($this->word as $letter => $reveal)
    {
      $this->word[$letter] = true;
    }
  }

  /*
  Determines if the game is over. Tests if either hangcount is
  equal to 6 or if every value in $word is TRUE.
  */
  public function isGameOver()
  {
    $count = 0;
    foreach($this->word as $letter => $reveal)
    {
      if($reveal == true)
      {
        $count++;
      }
    }

    return $this->hangCount == 6 ||
      $count == count($this->word);
  }

  /*
  Determines if the user won or lost.
  */
  public function isWinner()
  {
    return ! ($this->hangCount == 6);
  }

  /*
  Returns true if action is set to new_game.
  */
  public function isNewGame()
  {
    return $_GET['action'] == 'new_game';
  }

  /*
  Returns true if action is set to guessL.
  */
  public function isGuessL()
  {
    return $_GET['action'] == 'guessL';
  }

  /*
  Returns the current hang count.
  */
  public function getHangCount()
  {
    return $this->hangCount;
  }

  /*
  Returns the current word to guess and its
  associated boolean panel values.
  */
  public function getWord()
  {
    return $this->word;
  }

  /*
  Returns the current work bank.
  */
  public function getBank()
  {
    return $this->bank;
  }
}
?>
