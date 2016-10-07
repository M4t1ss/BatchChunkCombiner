
MODELFILE=$1;
DATA="$2";

echo $DATA | sed -e '$a\' | ./query -v summary $MODELFILE | egrep "^(Perplexity excluding OOVs)" | sed -e 's/Perplexity excluding OOVs:     //g'
