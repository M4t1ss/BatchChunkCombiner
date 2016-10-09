MODELFILE=$1;
DATA="$2";
T1="test";
T2=".txt";
TEMPFILE=${T1}${RANDOM}${T2};
VOCABULARY=$3;
RWTHLM_DIR=$4;

echo $DATA > ./test/$TEMPFILE;

$RWTHLM_DIR/rwthlm \
    --vocab $VOCABULARY \
    --unk \
    --ppl ./test/$TEMPFILE \
    --verbose \
    $MODELFILE | tail -n 1;

rm ./test/$TEMPFILE;